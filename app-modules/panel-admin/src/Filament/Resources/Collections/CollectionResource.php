<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Collections;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Collections\Pages\CreateCollection;
use He4rt\Admin\Filament\Resources\Collections\Pages\EditCollection;
use He4rt\Admin\Filament\Resources\Collections\Pages\ListCollections;
use He4rt\Admin\Filament\Resources\Collections\RelationManagers\EntriesRelationManager;
use He4rt\Admin\Filament\Resources\Collections\Schemas\CollectionForm;
use He4rt\Admin\Filament\Resources\Collections\Tables\CollectionsTable;
use He4rt\Catalog\Models\Collection;
use UnitEnum;

/**
 * Gestão de Trilhas (Coleções): metadados + a trilha ordenada de Entradas
 * (anexar, remover e reordenar pela posição do pivot).
 */
class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $slug = 'catalog/collections';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalog;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionsTable::table($table);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            EntriesRelationManager::class,
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListCollections::route('/'),
            'create' => CreateCollection::route('/create'),
            'edit' => EditCollection::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'summary'];
    }

    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.collections.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.collections.plural');
    }
}
