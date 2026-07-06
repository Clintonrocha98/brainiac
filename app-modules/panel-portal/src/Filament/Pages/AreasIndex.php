<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use He4rt\Portal\Support\CardChip;
use He4rt\Portal\Support\ContextCard;

final class AreasIndex extends ContextIndexPage
{
    protected static ?string $slug = 'areas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('panel-portal::portal.nav.areas');
    }

    public function getTitle(): string
    {
        return __('panel-portal::portal.index.area.title');
    }

    public function getSubheading(): string
    {
        return __('panel-portal::portal.index.area.subtitle');
    }

    /**
     * @return array<int, ContextCard>
     */
    protected function cards(): array
    {
        /** @var array<string, int> $documentCounts */
        $documentCounts = Entry::query()
            ->selectRaw('department, count(*) as total')
            ->groupBy('department')
            ->pluck('total', 'department')
            ->all();

        $collections = CatalogCollection::query()->get();

        return collect(Area::cases())
            ->map(static function (Area $area) use ($documentCounts, $collections): ContextCard {
                $trailCount = $collections
                    ->filter(static fn (CatalogCollection $collection): bool => $collection->isVisibleToArea($area))
                    ->count();

                return new ContextCard(
                    badge: mb_strtoupper(mb_substr($area->getLabel(), 0, 2)),
                    title: $area->getLabel(),
                    description: __('panel-portal::portal.areas_desc.'.$area->value),
                    meta: __('panel-portal::portal.index.area.meta', ['count' => $documentCounts[$area->value] ?? 0]),
                    url: ShowArea::getUrl(['area' => $area]),
                    chips: $trailCount > 0
                        ? [new CardChip(trans_choice('panel-portal::portal.index.area.chip_trails', $trailCount, ['count' => $trailCount]), 'gray')]
                        : [],
                );
            })
            ->all();
    }
}
