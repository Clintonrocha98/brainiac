<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Portal\Support\CardChip;
use He4rt\Portal\Support\ContextCard;

final class CollectionsIndex extends ContextIndexPage
{
    protected static ?string $slug = 'collections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('panel-portal::portal.nav.collections');
    }

    public function getTitle(): string
    {
        return __('panel-portal::portal.index.collection.title');
    }

    public function getSubheading(): string
    {
        return __('panel-portal::portal.index.collection.subtitle');
    }

    /**
     * @return array<int, ContextCard>
     */
    protected function cards(): array
    {
        return CatalogCollection::query()
            ->withCount('entries')
            ->orderBy('title')
            ->get()
            ->map(static fn (CatalogCollection $collection): ContextCard => new ContextCard(
                badge: '»',
                title: $collection->title,
                description: $collection->summary,
                meta: __('panel-portal::portal.index.collection.meta', ['count' => $collection->entries_count]),
                url: ShowCollection::getUrl(['collection' => $collection]),
                chips: $collection->audience
                    ->map(static fn (Audience $audience): CardChip => new CardChip($audience->getLabel(), 'gray'))
                    ->all(),
            ))
            ->all();
    }
}
