<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Project;

class EntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel-admin::catalog.entries.sections.identification'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('native_id')
                                ->label(__('panel-admin::catalog.entries.fields.native_id'))
                                ->helperText(__('panel-admin::catalog.entries.fields.native_id_helper'))
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('qualified_id')
                                ->label(__('panel-admin::catalog.entries.fields.qualified_id'))
                                ->disabled()
                                ->dehydrated(condition: false)
                                ->visibleOn('edit')
                                ->columnSpan(1),

                            TextInput::make('title')
                                ->label(__('panel-admin::catalog.entries.fields.title'))
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('summary')
                                ->label(__('panel-admin::catalog.entries.fields.summary'))
                                ->rows(3)
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make(__('panel-admin::catalog.entries.sections.classification'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('purpose')
                                ->label(__('panel-admin::catalog.entries.fields.purpose'))
                                ->options(Purpose::class)
                                ->required(),

                            Select::make('format')
                                ->label(__('panel-admin::catalog.entries.fields.format'))
                                ->options(Format::class)
                                ->required(),

                            Select::make('department')
                                ->label(__('panel-admin::catalog.entries.fields.department'))
                                ->options(Area::class)
                                ->required(),

                            Select::make('status')
                                ->label(__('panel-admin::catalog.entries.fields.status'))
                                ->options(Status::class)
                                ->default(Status::Draft->value)
                                ->required(),

                            Select::make('audience')
                                ->label(__('panel-admin::catalog.entries.fields.audience'))
                                ->options(Audience::class)
                                ->multiple()
                                ->required(),

                            Select::make('owner_id')
                                ->label(__('panel-admin::catalog.entries.fields.owner'))
                                ->relationship('owner', 'name')
                                ->required(),

                            Select::make('subject_project_ids')
                                ->label(__('panel-admin::catalog.entries.fields.subject_projects'))
                                ->options(fn (): array => Project::query()->orderBy('business_name')->pluck('business_name', 'id')->all())
                                ->multiple(),

                            TagsInput::make('keywords')
                                ->label(__('panel-admin::catalog.entries.fields.keywords')),
                        ]),
                    ]),

                Section::make(__('panel-admin::catalog.entries.sections.content'))
                    ->columnSpanFull()
                    ->schema([
                        MarkdownEditor::make('body_markdown')
                            ->label(__('panel-admin::catalog.entries.fields.body'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
