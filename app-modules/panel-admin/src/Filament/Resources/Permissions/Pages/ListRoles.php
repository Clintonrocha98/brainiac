<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Permissions\RoleResource;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    /**
     * @return CreateAction[]
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
