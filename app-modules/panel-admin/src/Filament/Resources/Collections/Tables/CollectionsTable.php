<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Collections\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('panel-admin::catalog.collections.fields.title'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('panel-admin::catalog.collections.fields.slug'))
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('audience')
                    ->label(__('panel-admin::catalog.collections.fields.audience'))
                    ->badge(),

                TextColumn::make('entries_count')
                    ->label(__('panel-admin::catalog.collections.fields.entries_count'))
                    ->counts('entries'),

                TextColumn::make('status')
                    ->label(__('panel-admin::catalog.collections.fields.status'))
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('title');
    }
}
