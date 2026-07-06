<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Collections\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Catalog\Models\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Trilha ordenada da Coleção: anexa Entradas existentes com a próxima
 * posição e permite reordenar por arrastar-e-soltar (coluna position do
 * pivot).
 */
class EntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('panel-admin::catalog.collections.entries_relation.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pivot.position')
                    ->label(__('panel-admin::catalog.collections.entries_relation.fields.position'))
                    ->fontFamily('mono'),

                TextColumn::make('qualified_id')
                    ->label(__('panel-admin::catalog.entries.fields.qualified_id'))
                    ->fontFamily('mono'),

                TextColumn::make('title')
                    ->label(__('panel-admin::catalog.entries.fields.title'))
                    ->searchable(),

                TextColumn::make('purpose')
                    ->label(__('panel-admin::catalog.entries.fields.purpose'))
                    ->badge(),
            ])
            ->reorderable('position')
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title', 'qualified_id'])
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'position' => $this->nextPosition(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }

    private function nextPosition(): int
    {
        $collection = $this->getOwnerRecord();

        if (!$collection instanceof Collection) {
            return 1;
        }

        $highestPosition = $collection->entries()->max('catalog_collection_entry.position');

        return is_numeric($highestPosition) ? ((int) $highestPosition) + 1 : 1;
    }
}
