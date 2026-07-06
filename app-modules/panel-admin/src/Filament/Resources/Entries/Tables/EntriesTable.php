<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;

class EntriesTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('qualified_id')
                    ->label(__('panel-admin::catalog.entries.fields.qualified_id'))
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('panel-admin::catalog.entries.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('purpose')
                    ->label(__('panel-admin::catalog.entries.fields.purpose'))
                    ->badge(),

                TextColumn::make('department')
                    ->label(__('panel-admin::catalog.entries.fields.department'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('origin')
                    ->label(__('panel-admin::catalog.entries.fields.origin'))
                    ->badge(),

                TextColumn::make('status')
                    ->label(__('panel-admin::catalog.entries.fields.status'))
                    ->badge(),

                TextColumn::make('owner.name')
                    ->label(__('panel-admin::catalog.entries.fields.owner'))
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label(__('panel-admin::catalog.entries.fields.updated_at'))
                    ->dateTime(timezone: config('app.display_timezone'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('purpose')
                    ->label(__('panel-admin::catalog.entries.fields.purpose'))
                    ->options(Purpose::class),

                SelectFilter::make('format')
                    ->label(__('panel-admin::catalog.entries.fields.format'))
                    ->options(Format::class),

                SelectFilter::make('department')
                    ->label(__('panel-admin::catalog.entries.fields.department'))
                    ->options(Area::class),

                SelectFilter::make('origin')
                    ->label(__('panel-admin::catalog.entries.fields.origin'))
                    ->options(Origin::class),

                SelectFilter::make('status')
                    ->label(__('panel-admin::catalog.entries.fields.status'))
                    ->options(Status::class),

                SelectFilter::make('projects')
                    ->label(__('panel-admin::catalog.entries.fields.subject_projects'))
                    ->relationship('projects', 'business_name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
