<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\DisplayDate;
use He4rt\Portal\Support\EntryAuthorship;
use He4rt\Portal\Support\EntryLinks;
use He4rt\Portal\Support\Markdown;
use He4rt\Portal\Support\PrdVersionStack;
use He4rt\Portal\Support\SourceLink;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

/**
 * Leitor de documento: corpo em prose, badges de faceta, ligações tipadas,
 * artefatos, anterior/próximo e o rail lateral (sumário, versões de PRD,
 * "sobre este doc" e "ver na fonte").
 */
abstract class ShowEntryPage extends PortalContextPage
{
    #[Locked]
    public Entry $entry;

    #[Url(as: 'v')]
    public ?string $version = null;

    protected string $view = 'panel-portal::filament.pages.show-entry';

    public function mount(?Project $project = null, ?Area $area = null, ?CatalogCollection $collection = null, ?Entry $entry = null): void
    {
        parent::mount($project, $area, $collection);

        abort_unless($entry instanceof Entry, 404);
        abort_unless($this->contextEntries()->contains('id', $entry->id), 404);

        $this->entry = $entry;
    }

    public function selectVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getTitle(): string
    {
        return $this->entry->title;
    }

    public function getSubheading(): ?string
    {
        return $this->entry->summary;
    }

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $context = $this->context();

        return [
            $context->indexUrl() => $context->typeLabel(),
            $context->overviewUrl() => $context->name(),
            $this->entry->title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $context = $this->context();
        $entries = $this->contextEntries();
        $flat = $context->flatEntries($entries);

        $position = $flat->search(fn (Entry $candidate): bool => $candidate->id === $this->entry->id);
        $previous = is_int($position) && $position > 0 ? $flat[$position - 1] : null;
        $next = is_int($position) && $position < $flat->count() - 1 ? $flat[$position + 1] : null;

        $this->entry->loadMissing(['document', 'originProject', 'owner', 'projects', 'artifacts', 'prdVersions']);

        $versionStack = PrdVersionStack::of($this->entry, $this->version);
        $bodyMarkdown = $versionStack->isEmpty()
            ? $this->entry->document?->body_markdown
            : $versionStack->bodyMarkdown();
        $markdown = resolve(Markdown::class);

        return [
            'entry' => $this->entry,
            'context' => $context,
            'bodyHtml' => $bodyMarkdown !== null ? $markdown->toHtml($bodyMarkdown) : null,
            'toc' => $bodyMarkdown !== null ? $markdown->toc($bodyMarkdown) : [],
            'authorship' => EntryAuthorship::of($this->entry),
            'updatedAt' => DisplayDate::short($this->entry->updated_at),
            'audienceText' => $this->entry->audience
                ->map(static fn (Audience $audience): string => $audience->getLabel())
                ->implode(', '),
            'links' => resolve(EntryLinks::class)->for($this->entry, $context, $flat),
            'previous' => $previous,
            'next' => $next,
            'sourceUrl' => SourceLink::for($this->entry),
            'versionStack' => $versionStack,
            'selectedFrozenAt' => DisplayDate::short($versionStack->selected?->frozen_at),
        ];
    }
}
