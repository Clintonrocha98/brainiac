<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

final class ShowCollectionEntry extends ShowEntryPage
{
    protected static ?string $slug = 'collections/{collection:slug}/e/{entry}';
}
