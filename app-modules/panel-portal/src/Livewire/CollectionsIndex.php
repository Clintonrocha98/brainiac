<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('panel-portal::layouts.portal')]
final class CollectionsIndex extends Component
{
    public function render(): View
    {
        $cards = CatalogCollection::query()
            ->withCount('entries')
            ->orderBy('title')
            ->get()
            ->map(static fn (CatalogCollection $collection): array => [
                'badge' => '»',
                'title' => $collection->title,
                'description' => $collection->summary,
                'meta' => __('panel-portal::portal.index.collection.meta', ['count' => $collection->entries_count]),
                'chips' => $collection->audience
                    ->map(static fn (Audience $audience): array => ['label' => $audience->getLabel(), 'style' => 'neutral'])
                    ->all(),
                'url' => route('portal.collections.show', ['collection' => $collection]),
            ])
            ->all();

        return view('panel-portal::livewire.context-index', [
            'title' => __('panel-portal::portal.index.collection.title'),
            'subtitle' => __('panel-portal::portal.index.collection.subtitle'),
            'cards' => $cards,
        ])->title(__('panel-portal::portal.index.collection.title').' · Brainiac');
    }
}
