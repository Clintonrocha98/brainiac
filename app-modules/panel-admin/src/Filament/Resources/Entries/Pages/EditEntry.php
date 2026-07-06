<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Entries\EntryResource;
use He4rt\Catalog\Actions\UpdateNativeEntry;
use He4rt\Catalog\DTOs\NativeEntryData;
use He4rt\Catalog\Models\Entry;
use Illuminate\Database\Eloquent\Model;

class EditEntry extends EditRecord
{
    protected static string $resource = EntryResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Campos virtuais do form (corpo do Documento e faceta projeto) não são
     * colunas da Entrada — carrega dos relacionamentos.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Entry $entry */
        $entry = $this->getRecord();

        $data['body_markdown'] = $entry->document?->body_markdown;
        $data['subject_project_ids'] = $entry->projects->pluck('id')->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Entry $record */
        return resolve(UpdateNativeEntry::class)->execute($record, NativeEntryData::fromForm($data));
    }
}
