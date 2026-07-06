<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Entries\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Entries\EntryResource;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
