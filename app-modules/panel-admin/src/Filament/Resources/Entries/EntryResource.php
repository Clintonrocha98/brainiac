<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Entries\Pages\CreateEntry;
use He4rt\Admin\Filament\Resources\Entries\Pages\EditEntry;
use He4rt\Admin\Filament\Resources\Entries\Pages\ListEntries;
use He4rt\Admin\Filament\Resources\Entries\RelationManagers\PrdVersionsRelationManager;
use He4rt\Admin\Filament\Resources\Entries\Schemas\EntryForm;
use He4rt\Admin\Filament\Resources\Entries\Tables\EntriesTable;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Entry;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Autoria de Entradas NATIVAS. Espelhos (federação) são somente leitura:
 * aparecem na listagem, mas não podem ser editados nem removidos por aqui.
 */
class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $slug = 'catalog/entries';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalog;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntriesTable::table($table);
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof Entry && $record->origin === Origin::Native;
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof Entry && $record->origin === Origin::Native;
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            PrdVersionsRelationManager::class,
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListEntries::route('/'),
            'create' => CreateEntry::route('/create'),
            'edit' => EditEntry::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'qualified_id', 'summary'];
    }

    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.entries.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.entries.plural');
    }
}
