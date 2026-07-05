<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use Illuminate\Support\Str;

/**
 * Render de markdown do portal (fatia enxuta): CommonMark seguro + pós-processamento
 * fiel ao design — mermaid vira bloco sinalizado (não desenhado), imagem vira
 * placeholder e h1–h3 ganham id para o sumário ("Nesta página").
 */
final class Markdown
{
    public function toHtml(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $this->wrapMermaidBlocks($html);
        $html = $this->replaceImagesWithPlaceholders($html);

        return $this->addHeadingAnchors($html);
    }

    /**
     * Sumário: apenas os h2 (`## `), como no design; ignora headings em code fences.
     *
     * @return array<int, array{text: string, slug: string}>
     */
    public function toc(string $markdown): array
    {
        $items = [];
        $insideFence = false;

        foreach (explode("\n", $markdown) as $line) {
            if (str_starts_with(mb_trim($line), '```')) {
                $insideFence = !$insideFence;

                continue;
            }

            if (!$insideFence && str_starts_with($line, '## ')) {
                $text = mb_trim(mb_substr($line, 3));
                $items[] = ['text' => $text, 'slug' => Str::slug($text)];
            }
        }

        return $items;
    }

    private function wrapMermaidBlocks(string $html): string
    {
        $label = e(__('panel-portal::portal.markdown.mermaid_label'));

        $replaced = preg_replace(
            '#<pre><code class="language-mermaid">(.*?)</code></pre>#s',
            '<div class="portal-mermaid"><div class="portal-mermaid-label">'.$label.'</div><pre>$1</pre></div>',
            $html,
        );

        return $replaced ?? $html;
    }

    private function replaceImagesWithPlaceholders(string $html): string
    {
        $replaced = preg_replace_callback(
            '#(?:<p>)?<img[^>]*alt="([^"]*)"[^>]*/?>(?:</p>)?#',
            static fn (array $matches): string => '<div class="portal-image-placeholder">'
                .e(__('panel-portal::portal.markdown.image_label', ['alt' => $matches[1]]))
                .'</div>',
            $html,
        );

        return $replaced ?? $html;
    }

    private function addHeadingAnchors(string $html): string
    {
        $replaced = preg_replace_callback(
            '#<h([123])>(.*?)</h\1>#s',
            static fn (array $matches): string => sprintf(
                '<h%1$s id="%2$s">%3$s</h%1$s>',
                $matches[1],
                Str::slug(strip_tags($matches[2])),
                $matches[2],
            ),
            $html,
        );

        return $replaced ?? $html;
    }
}
