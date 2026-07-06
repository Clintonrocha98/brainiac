<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\PortalContext;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Locked;

/**
 * Base das páginas que vivem dentro de um contexto (Projeto, Área ou Trilha).
 * Resolve o contexto a partir do parâmetro de rota e monta a sub-navegação
 * lateral com as entradas agrupadas por propósito (ou pela ordem da trilha).
 */
abstract class PortalContextPage extends Page
{
    #[Locked]
    public ?Project $project = null;

    #[Locked]
    public ?Area $area = null;

    #[Locked]
    public ?CatalogCollection $collection = null;

    protected static bool $shouldRegisterNavigation = false;

    protected Width|string|null $maxContentWidth = Width::Container;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    private ?PortalContext $resolvedContext = null;

    /** @var EloquentCollection<int, Entry>|null */
    private ?EloquentCollection $resolvedEntries = null;

    public function mount(?Project $project = null, ?Area $area = null, ?CatalogCollection $collection = null): void
    {
        $this->project = $project;
        $this->area = $area;
        $this->collection = $collection;
    }

    /**
     * @return array<NavigationItem | NavigationGroup>
     */
    public function getSubNavigation(): array
    {
        $context = $this->context();

        $overview = NavigationItem::make(__('panel-portal::portal.context.overview'))
            ->icon(Heroicon::OutlinedHome)
            ->url($context->overviewUrl())
            ->isActiveWhen(fn (): bool => url()->current() === $context->overviewUrl());

        $groups = collect($context->navGroups($this->contextEntries()))
            ->map(static function (array $group) use ($context): NavigationGroup {
                $items = $group['entries']
                    ->values()
                    ->map(static function (Entry $entry, int $index) use ($group, $context): NavigationItem {
                        $url = $context->entryUrl($entry);
                        $mirrorBadge = $entry->origin === Origin::Mirror
                            ? __('panel-portal::portal.context.mirror_badge')
                            : null;

                        return NavigationItem::make($group['positioned'] ? sprintf('%02d · %s', $index + 1,
                            $entry->title) : $entry->title)
                            ->icon($entry->format->getIcon())
                            ->url($url)
                            ->badge(is_string($mirrorBadge) ? $mirrorBadge : null, color: 'info')
                            ->isActiveWhen(fn (): bool => url()->current() === $url);
                    })
                    ->all();

                return NavigationGroup::make($group['label'])->items($items);
            })
            ->all();

        return [$overview, ...$groups];
    }

    protected function context(): PortalContext
    {
        return $this->resolvedContext ??= match (true) {
            $this->project instanceof Project => PortalContext::forProject($this->project),
            $this->area instanceof Area => PortalContext::forArea($this->area),
            $this->collection instanceof CatalogCollection => PortalContext::forCollection($this->collection),
            default => abort(404),
        };
    }

    /**
     * @return EloquentCollection<int, Entry>
     */
    protected function contextEntries(): EloquentCollection
    {
        return $this->resolvedEntries ??= $this->context()->entries();
    }
}
