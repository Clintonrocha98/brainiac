<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Entries\EntryResource;
use He4rt\Catalog\Actions\CreateNativeEntry;
use He4rt\Catalog\DTOs\NativeEntryData;
use Illuminate\Database\Eloquent\Model;

class CreateEntry extends CreateRecord
{
    protected static string $resource = EntryResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return resolve(CreateNativeEntry::class)->execute(NativeEntryData::fromForm($data));
    }
}
