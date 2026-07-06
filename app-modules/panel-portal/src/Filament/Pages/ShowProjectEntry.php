<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

final class ShowProjectEntry extends ShowEntryPage
{
    protected static ?string $slug = 'projects/{project:slug}/e/{entry}';
}
