<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Collections\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Collections\CollectionResource;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;
}
