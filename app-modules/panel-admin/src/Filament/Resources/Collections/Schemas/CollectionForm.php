<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Collections\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('panel-admin::catalog.collections.fields.title'))
                            ->required(),

                        TextInput::make('slug')
                            ->label(__('panel-admin::catalog.collections.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('summary')
                            ->label(__('panel-admin::catalog.collections.fields.summary'))
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),

                        Select::make('audience')
                            ->label(__('panel-admin::catalog.collections.fields.audience'))
                            ->options(Audience::class)
                            ->multiple()
                            ->required(),

                        Select::make('owner_id')
                            ->label(__('panel-admin::catalog.collections.fields.owner'))
                            ->relationship('owner', 'name')
                            ->required(),

                        Select::make('status')
                            ->label(__('panel-admin::catalog.collections.fields.status'))
                            ->options(Status::class)
                            ->default(Status::Draft->value)
                            ->required(),

                        MarkdownEditor::make('body_markdown')
                            ->label(__('panel-admin::catalog.collections.fields.body'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
