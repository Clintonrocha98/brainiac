<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Catalog\Models\Project;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel-admin::catalog.projects.sections.identification'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('business_name')
                                ->label(__('panel-admin::catalog.projects.fields.business_name'))
                                ->required(),

                            TextInput::make('technical_name')
                                ->label(__('panel-admin::catalog.projects.fields.technical_name'))
                                ->required(),

                            TextInput::make('slug')
                                ->label(__('panel-admin::catalog.projects.fields.slug'))
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('acronym')
                                ->label(__('panel-admin::catalog.projects.fields.acronym'))
                                ->helperText(__('panel-admin::catalog.projects.fields.acronym_helper'))
                                ->required()
                                ->maxLength(8)
                                ->unique(ignoreRecord: true),
                        ]),
                    ]),

                Section::make(__('panel-admin::catalog.projects.sections.federation'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('repo_url')
                                ->label(__('panel-admin::catalog.projects.fields.repo_url'))
                                ->url(),

                            TextInput::make('default_branch')
                                ->label(__('panel-admin::catalog.projects.fields.default_branch'))
                                ->placeholder('main'),

                            TextEntry::make('last_synced_at')
                                ->label(__('panel-admin::catalog.projects.fields.last_synced_at'))
                                ->state(fn (?Project $record): string => $record?->last_synced_at
                                    ?->timezone(config('app.display_timezone'))->format('d/m/Y H:i')
                                    ?? __('panel-admin::catalog.projects.fields.never_synced'))
                                ->visibleOn('edit'),
                        ]),
                    ]),
            ]);
    }
}
