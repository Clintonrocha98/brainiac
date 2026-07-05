<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasLabel
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Obsolete = 'obsolete';

    public function getLabel(): string
    {
        return __("catalog::enums.status.{$this->value}");
    }
}
