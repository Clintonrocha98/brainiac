<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Purpose: string implements HasLabel
{
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';

    public function getLabel(): string
    {
        return __('catalog::enums.purpose.'.$this->value);
    }
}
