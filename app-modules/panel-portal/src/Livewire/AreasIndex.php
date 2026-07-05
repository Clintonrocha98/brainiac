<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Catalog\Models\Entry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('panel-portal::layouts.portal')]
final class AreasIndex extends Component
{
    public function render(): View
    {
        /** @var array<string, int> $counts */
        $counts = Entry::query()
            ->selectRaw('department, count(*) as total')
            ->groupBy('department')
            ->pluck('total', 'department')
            ->all();

        $collections = CatalogCollection::query()->get();

        $cards = collect(Area::cases())
            ->map(static function (Area $area) use ($counts, $collections): array {
                $trails = $collections->filter(
                    static fn (CatalogCollection $collection): bool => $collection->audience->contains(Audience::from($area->value))
                        || $collection->audience->contains(Audience::All),
                )->count();

                return [
                    'badge' => mb_strtoupper(mb_substr($area->getLabel(), 0, 2)),
                    'title' => $area->getLabel(),
                    'description' => __('panel-portal::portal.areas_desc.'.$area->value),
                    'meta' => __('panel-portal::portal.index.area.meta', ['count' => $counts[$area->value] ?? 0]),
                    'chips' => $trails > 0
                        ? [['label' => trans_choice('panel-portal::portal.index.area.chip_trails', $trails, ['count' => $trails]), 'style' => 'neutral']]
                        : [],
                    'url' => route('portal.areas.show', ['area' => $area->value]),
                ];
            })
            ->all();

        return view('panel-portal::livewire.context-index', [
            'title' => __('panel-portal::portal.index.area.title'),
            'subtitle' => __('panel-portal::portal.index.area.subtitle'),
            'cards' => $cards,
        ])->title(__('panel-portal::portal.index.area.title').' · Brainiac');
    }
}
