<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Contexto de navegação do portal: um Projeto, uma Área (departamento) ou uma
 * Coleção (trilha). Resolve identidade, URLs e a navegação lateral — entradas
 * agrupadas por propósito (Diátaxis) ou pela posição da trilha.
 */
final readonly class PortalContext
{
    private function __construct(
        public ContextType $type,
        public ?Project $project = null,
        public ?Area $area = null,
        public ?CatalogCollection $collection = null,
    ) {}

    public static function forProject(Project $project): self
    {
        return new self(ContextType::Project, project: $project);
    }

    public static function forArea(Area $area): self
    {
        return new self(ContextType::Area, area: $area);
    }

    public static function forCollection(CatalogCollection $collection): self
    {
        return new self(ContextType::Collection, collection: $collection);
    }

    public function name(): string
    {
        return match ($this->type) {
            ContextType::Project => (string) $this->project?->business_name,
            ContextType::Area => (string) $this->area?->getLabel(),
            ContextType::Collection => (string) $this->collection?->title,
        };
    }

    public function badge(): string
    {
        return match ($this->type) {
            ContextType::Project => (string) $this->project?->acronym,
            ContextType::Area => mb_strtoupper(mb_substr((string) $this->area?->getLabel(), 0, 2)),
            ContextType::Collection => '»',
        };
    }

    public function typeLabel(): string
    {
        return __('panel-portal::portal.context.type.'.$this->type->value);
    }

    public function backLabel(): string
    {
        return __('panel-portal::portal.context.back.'.$this->type->value);
    }

    public function indexUrl(): string
    {
        return match ($this->type) {
            ContextType::Project => route('portal.projects.index'),
            ContextType::Area => route('portal.areas.index'),
            ContextType::Collection => route('portal.collections.index'),
        };
    }

    public function overviewUrl(): string
    {
        return match ($this->type) {
            ContextType::Project => route('portal.projects.show', ['project' => $this->project]),
            ContextType::Area => route('portal.areas.show', ['area' => $this->area]),
            ContextType::Collection => route('portal.collections.show', ['collection' => $this->collection]),
        };
    }

    public function entryUrl(Entry $entry): string
    {
        return match ($this->type) {
            ContextType::Project => route('portal.projects.entry', ['project' => $this->project, 'entry' => $entry]),
            ContextType::Area => route('portal.areas.entry', ['area' => $this->area, 'entry' => $entry]),
            ContextType::Collection => route('portal.collections.entry', ['collection' => $this->collection, 'entry' => $entry]),
        };
    }

    /**
     * Entradas do contexto: faceta projeto (assunto), departamento dono ou trilha ordenada.
     *
     * @return EloquentCollection<int, Entry>
     */
    public function entries(): EloquentCollection
    {
        return match ($this->type) {
            ContextType::Project => $this->project instanceof Project
                ? $this->project->taggedEntries()->orderBy('title')->get()
                : new EloquentCollection,
            ContextType::Area => Entry::query()->where('department', $this->area)->orderBy('title')->get(),
            ContextType::Collection => $this->collection instanceof CatalogCollection
                ? $this->collection->entries()->get()
                : new EloquentCollection,
        };
    }

    /**
     * Navegação lateral: trilha vira um grupo posicionado; os demais contextos
     * agrupam por propósito na ordem canônica (referência, how-to, explicação).
     *
     * @param  EloquentCollection<int, Entry>  $entries
     * @return array<int, array{label: string, positioned: bool, entries: EloquentCollection<int, Entry>}>
     */
    public function navGroups(EloquentCollection $entries): array
    {
        if ($this->type === ContextType::Collection) {
            return [[
                'label' => __('panel-portal::portal.context.trail_group'),
                'positioned' => true,
                'entries' => $entries,
            ]];
        }

        $groups = [];

        foreach (Purpose::cases() as $purpose) {
            $ofPurpose = $entries->filter(fn (Entry $entry): bool => $entry->purpose === $purpose)->values();

            if ($ofPurpose->isNotEmpty()) {
                $groups[] = [
                    'label' => $purpose->getLabel(),
                    'positioned' => false,
                    'entries' => $ofPurpose,
                ];
            }
        }

        return $groups;
    }

    /**
     * Ordem achatada da navegação — base do anterior/próximo do leitor.
     *
     * @param  EloquentCollection<int, Entry>  $entries
     * @return EloquentCollection<int, Entry>
     */
    public function flatEntries(EloquentCollection $entries): EloquentCollection
    {
        $flat = new EloquentCollection;

        foreach ($this->navGroups($entries) as $group) {
            $flat = $flat->merge($group['entries']);
        }

        return $flat;
    }
}
