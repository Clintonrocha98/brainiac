<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use Carbon\CarbonInterface;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;
use He4rt\Catalog\Models\PrdVersion;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\Markdown;
use He4rt\Portal\Support\PortalContext;
use He4rt\Portal\Support\SourceLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Leitor de documento: corpo em prose, badges de faceta, ligações tipadas,
 * artefatos, anterior/próximo e o rail lateral (sumário, versões de PRD,
 * "sobre este doc" e "ver na fonte").
 */
#[Layout('panel-portal::layouts.portal')]
final class ShowEntry extends Component
{
    #[Locked]
    public Entry $entry;

    #[Locked]
    public ?Project $project = null;

    #[Locked]
    public ?Area $area = null;

    #[Locked]
    public ?CatalogCollection $collection = null;

    #[Url(as: 'v')]
    public ?string $version = null;

    public function mount(Entry $entry, ?Project $project = null, ?Area $area = null, ?CatalogCollection $collection = null): void
    {
        $this->entry = $entry;
        $this->project = $project;
        $this->area = $area;
        $this->collection = $collection;
    }

    public function selectVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function render(): View
    {
        $context = $this->context();
        $entries = $context->entries();

        abort_unless($entries->contains('id', $this->entry->id), 404);

        $groups = $context->navGroups($entries);
        $flat = $context->flatEntries($entries);

        $position = $flat->search(fn (Entry $candidate): bool => $candidate->id === $this->entry->id);
        $previous = is_int($position) && $position > 0 ? $flat[$position - 1] : null;
        $next = is_int($position) && $position < $flat->count() - 1 ? $flat[$position + 1] : null;

        $this->entry->loadMissing(['document', 'originProject', 'owner', 'projects', 'artifacts', 'prdVersions']);

        $versions = $this->entry->prdVersions
            ->sortBy([['major', 'desc'], ['minor', 'desc']])
            ->values();
        $selected = $this->selectedVersion($versions);

        $bodyMarkdown = $selected instanceof PrdVersion
            ? $selected->body_markdown
            : $this->entry->document?->body_markdown;
        $markdown = resolve(Markdown::class);

        $groupLabel = collect($groups)
            ->first(fn (array $group): bool => $group['entries']->contains('id', $this->entry->id))['label'] ?? '—';

        return view('panel-portal::livewire.show-entry', [
            'context' => $context,
            'groups' => $groups,
            'groupLabel' => $groupLabel,
            'bodyHtml' => $bodyMarkdown !== null ? $markdown->toHtml($bodyMarkdown) : null,
            'toc' => $bodyMarkdown !== null ? $markdown->toc($bodyMarkdown) : [],
            'byLine' => $this->byLine(),
            'updatedAt' => $this->displayDate($this->entry->updated_at),
            'audienceText' => $this->entry->audience
                ->map(static fn (Audience $audience): string => $audience->getLabel())
                ->implode(', '),
            'authorshipLabel' => $this->entry->origin === Origin::Native
                ? __('panel-portal::portal.reader.owner')
                : __('panel-portal::portal.reader.authors'),
            'authorsText' => $this->authorsText(),
            'links' => $this->links($context, $flat),
            'previous' => $previous,
            'next' => $next,
            'sourceUrl' => SourceLink::for($this->entry),
            'versions' => $versions,
            'selectedVersion' => $selected,
            'selectedVersionLabel' => $selected instanceof PrdVersion ? $this->versionLabel($selected) : null,
            'latestVersionLabel' => $versions->isNotEmpty() ? $this->versionLabel($versions->first()) : null,
            'showOldVersionBanner' => $selected instanceof PrdVersion && $versions->isNotEmpty() && $selected->isNot($versions->first()),
            'versionOptions' => $versions->map(fn (PrdVersion $candidate): array => [
                'value' => $this->versionLabel($candidate),
                'label' => $this->versionLabel($candidate),
                'state' => $candidate->state,
                'meta' => $this->displayDate($candidate->frozen_at) ?? __('panel-portal::portal.prd.editing'),
                'selected' => $selected instanceof PrdVersion && $candidate->is($selected),
            ])->all(),
        ])->title($this->entry->title.' · Brainiac');
    }

    private function context(): PortalContext
    {
        return match (true) {
            $this->project instanceof Project => PortalContext::forProject($this->project),
            $this->area instanceof Area => PortalContext::forArea($this->area),
            $this->collection instanceof CatalogCollection => PortalContext::forCollection($this->collection),
            default => abort(404),
        };
    }

    /**
     * @param  EloquentCollection<int, PrdVersion>  $versions
     */
    private function selectedVersion(EloquentCollection $versions): ?PrdVersion
    {
        if ($versions->isEmpty()) {
            return null;
        }

        if ($this->version !== null) {
            $match = $versions->first(fn (PrdVersion $candidate): bool => $this->versionLabel($candidate) === $this->version);

            if ($match !== null) {
                return $match;
            }
        }

        return $versions->first();
    }

    private function versionLabel(PrdVersion $version): string
    {
        return sprintf('v%d.%d', $version->major ?? 0, $version->minor ?? 0);
    }

    private function byLine(): string
    {
        if ($this->entry->origin === Origin::Native) {
            return __('panel-portal::portal.reader.by', ['owner' => $this->entry->owner->name ?? '—']);
        }

        return collect($this->entry->authors ?? [])
            ->map(static fn (string $handle): string => '@'.$handle)
            ->implode(' · ');
    }

    private function authorsText(): string
    {
        if ($this->entry->origin === Origin::Native) {
            return $this->entry->owner->name ?? '—';
        }

        return collect($this->entry->authors ?? [])
            ->map(static fn (string $handle): string => '@'.$handle)
            ->implode(', ');
    }

    /**
     * Ligações tipadas nas duas direções, com rótulo conforme o sentido
     * (ex.: "Substitui" na saída, "Substituída por" na entrada).
     *
     * @param  EloquentCollection<int, Entry>  $flat
     * @return array<int, array{label: string, title: string, qid: string, url: string}>
     */
    private function links(PortalContext $context, EloquentCollection $flat): array
    {
        return EntryLink::query()
            ->where('from_entry_id', $this->entry->id)
            ->orWhere('to_entry_id', $this->entry->id)
            ->with(['fromEntry.projects', 'toEntry.projects'])
            ->get()
            ->map(function (EntryLink $link) use ($context, $flat): array {
                $outbound = $link->from_entry_id === $this->entry->id;
                $target = $outbound ? $link->toEntry : $link->fromEntry;

                return [
                    'label' => __('panel-portal::portal.links.'.$link->type->value.'.'.($outbound ? 'out' : 'in')),
                    'title' => $target->title,
                    'qid' => $target->qualified_id,
                    'url' => $this->entryUrlFor($target, $context, $flat),
                ];
            })
            ->all();
    }

    /**
     * @param  EloquentCollection<int, Entry>  $flat
     */
    private function entryUrlFor(Entry $target, PortalContext $context, EloquentCollection $flat): string
    {
        if ($flat->contains('id', $target->id)) {
            return $context->entryUrl($target);
        }

        $firstProject = $target->projects->first();

        if ($firstProject !== null) {
            return route('portal.projects.entry', ['project' => $firstProject, 'entry' => $target]);
        }

        return route('portal.areas.entry', ['area' => $target->department->value, 'entry' => $target]);
    }

    private function displayDate(?CarbonInterface $date): ?string
    {
        return $date?->timezone(config('app.display_timezone'))->translatedFormat('d M Y');
    }
}
