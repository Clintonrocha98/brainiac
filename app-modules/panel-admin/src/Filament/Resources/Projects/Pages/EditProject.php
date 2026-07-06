<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Projects\Actions\RotateFederationCredentialsAction;
use He4rt\Admin\Filament\Resources\Projects\ProjectResource;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            RotateFederationCredentialsAction::make(),
            DeleteAction::make(),
        ];
    }
}
