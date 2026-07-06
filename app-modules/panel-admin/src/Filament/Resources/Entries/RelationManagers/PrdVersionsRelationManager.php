<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Catalog\Actions\FreezePrdVersion;
use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pilha de versões do PRD. Só aparece em Entradas de formato PRD: publica
 * novas versões (rascunho) e congela versões existentes; congeladas são
 * imutáveis.
 */
class PrdVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'prdVersions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var Entry $ownerRecord */
        return $ownerRecord->format->isPrd();
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('panel-admin::catalog.prd_versions.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('major')
                        ->label(__('panel-admin::catalog.prd_versions.fields.major'))
                        ->integer()
                        ->minValue(0)
                        ->required(),

                    TextInput::make('minor')
                        ->label(__('panel-admin::catalog.prd_versions.fields.minor'))
                        ->integer()
                        ->minValue(0)
                        ->required(),
                ]),

                MarkdownEditor::make('body_markdown')
                    ->label(__('panel-admin::catalog.prd_versions.fields.body'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label(__('panel-admin::catalog.prd_versions.fields.version'))
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->state(fn (PrdVersion $record): string => sprintf('v%d.%d', $record->major ?? 0, $record->minor ?? 0)),

                TextColumn::make('state')
                    ->label(__('panel-admin::catalog.prd_versions.fields.state'))
                    ->badge(),

                TextColumn::make('frozen_at')
                    ->label(__('panel-admin::catalog.prd_versions.fields.frozen_at'))
                    ->dateTime(timezone: config('app.display_timezone'))
                    ->placeholder('—'),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderByDesc('major')->orderByDesc('minor'))
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'state' => PrdVersionState::Draft,
                        'frozen_at' => null,
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (PrdVersion $record): bool => $record->state === PrdVersionState::Draft),

                Action::make('freeze')
                    ->label(__('panel-admin::catalog.prd_versions.actions.freeze.label'))
                    ->icon(Heroicon::OutlinedLockClosed)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('panel-admin::catalog.prd_versions.actions.freeze.heading'))
                    ->modalDescription(__('panel-admin::catalog.prd_versions.actions.freeze.description'))
                    ->visible(fn (PrdVersion $record): bool => $record->state === PrdVersionState::Draft)
                    ->action(static function (PrdVersion $record): void {
                        resolve(FreezePrdVersion::class)->execute($record);

                        Notification::make()
                            ->success()
                            ->title(__('panel-admin::catalog.prd_versions.actions.freeze.success'))
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (PrdVersion $record): bool => $record->state === PrdVersionState::Draft),
            ]);
    }
}
