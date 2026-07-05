<?php

declare(strict_types=1);

use He4rt\Portal\Support\Markdown;

test('renders markdown to html with heading anchors', function (): void {
    $html = (new Markdown)->toHtml("## Métricas de sucesso\n\nTexto do corpo.");

    expect($html)
        ->toContain('<h2 id="metricas-de-sucesso">')
        ->toContain('Texto do corpo');
});

test('wraps mermaid blocks in a signaled container instead of rendering', function (): void {
    $html = (new Markdown)->toHtml("```mermaid\ngraph TD; A-->B;\n```");

    expect($html)
        ->toContain('portal-mermaid')
        ->toContain('portal-mermaid-label')
        ->toContain('graph TD;');
});

test('keeps regular fenced code as a plain pre block', function (): void {
    $html = (new Markdown)->toHtml("```bash\nmake up\n```");

    expect($html)
        ->toContain('<pre>')
        ->toContain('make up')
        ->not->toContain('portal-mermaid');
});

test('replaces images with placeholders', function (): void {
    $html = (new Markdown)->toHtml('![Fluxo de publicação](img/fluxo.png)');

    expect($html)
        ->toContain('portal-image-placeholder')
        ->toContain('Fluxo de publicação')
        ->not->toContain('<img');
});

test('strips raw html input', function (): void {
    $html = (new Markdown)->toHtml("<script>alert(1)</script>\n\nTexto seguro.");

    expect($html)
        ->not->toContain('<script>')
        ->toContain('Texto seguro');
});

test('toc lists only h2 headings outside code fences', function (): void {
    $toc = (new Markdown)->toc("## Um\n\n```\n## Dentro do fence\n```\n\n### Subtítulo\n\n## Dois");

    expect($toc)->toBe([
        ['text' => 'Um', 'slug' => 'um'],
        ['text' => 'Dois', 'slug' => 'dois'],
    ]);
});
