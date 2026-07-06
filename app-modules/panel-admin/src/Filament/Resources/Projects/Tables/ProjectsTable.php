<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Projects\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Projects\Actions\RotateFederationCredentialsAction;
use He4rt\Catalog\Models\Project;

class ProjectsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('acronym')
                    ->label(__('panel-admin::catalog.projects.fields.acronym'))
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('business_name')
                    ->label(__('panel-admin::catalog.projects.fields.business_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('technical_name')
                    ->label(__('panel-admin::catalog.projects.fields.technical_name'))
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('federation')
                    ->label(__('panel-admin::catalog.projects.fields.federation'))
                    ->badge()
                    ->state(fn (Project $record): string => filled($record->webhook_token)
                        ? __('panel-admin::catalog.projects.fields.federation_configured')
                        : __('panel-admin::catalog.projects.fields.federation_pending'))
                    ->color(fn (Project $record): string => filled($record->webhook_token) ? 'success' : 'gray'),

                TextColumn::make('last_synced_at')
                    ->label(__('panel-admin::catalog.projects.fields.last_synced_at'))
                    ->dateTime(timezone: config('app.display_timezone'))
                    ->placeholder(__('panel-admin::catalog.projects.fields.never_synced'))
                    ->sortable(),
            ])
            ->recordActions([
                RotateFederationCredentialsAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('business_name');
    }
}
