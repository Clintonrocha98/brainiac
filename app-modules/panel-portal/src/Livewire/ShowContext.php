<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\ContextType;
use He4rt\Portal\Support\Markdown;
use He4rt\Portal\Support\PortalContext;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Visão geral de um contexto (Projeto, Área ou Coleção/trilha): sidebar de
 * navegação + lista de documentos do contexto.
 */
#[Layout('panel-portal::layouts.portal')]
final class ShowContext extends Component
{
    #[Locked]
    public ?Project $project = null;

    #[Locked]
    public ?Area $area = null;

    #[Locked]
    public ?CatalogCollection $collection = null;

    public function mount(?Project $project = null, ?Area $area = null, ?CatalogCollection $collection = null): void
    {
        $this->project = $project;
        $this->area = $area;
        $this->collection = $collection;
    }

    public function render(): View
    {
        $context = $this->context();
        $entries = $context->entries();
        $groups = $context->navGroups($entries);
        $flat = $context->flatEntries($entries);

        $introHtml = null;

        if ($context->type === ContextType::Collection && $this->collection?->body_markdown !== null) {
            $introHtml = resolve(Markdown::class)->toHtml($this->collection->body_markdown);
        }

        return view('panel-portal::livewire.show-context', [
            'context' => $context,
            'groups' => $groups,
            'flat' => $flat,
            'subtitle' => $this->subtitle($context),
            'listLabel' => $context->type === ContextType::Collection
                ? __('panel-portal::portal.overview.trail_label', ['count' => $flat->count()])
                : __('panel-portal::portal.overview.docs_label', ['count' => $flat->count()]),
            'introHtml' => $introHtml,
            'trails' => $this->area instanceof Area ? $this->trailsOfArea($this->area) : collect(),
        ])->title($context->name().' · Brainiac');
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

    private function subtitle(PortalContext $context): string
    {
        return match ($context->type) {
            ContextType::Project => (string) $this->project?->technical_name,
            ContextType::Area => __('panel-portal::portal.overview.area_subtitle', ['area' => (string) $this->area?->getLabel()]),
            ContextType::Collection => (string) $this->collection?->summary,
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, CatalogCollection>
     */
    private function trailsOfArea(Area $area): \Illuminate\Support\Collection
    {
        return CatalogCollection::query()
            ->withCount('entries')
            ->where(static function (Builder $query) use ($area): void {
                $query
                    ->whereJsonContains('audience', $area->value)
                    ->orWhereJsonContains('audience', Audience::All->value);
            })
            ->orderBy('title')
            ->get()
            ->toBase();
    }
}
