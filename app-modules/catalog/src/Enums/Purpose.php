<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Purpose: string implements HasColor, HasLabel
{
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';

    public function getLabel(): string
    {
        return __('catalog::enums.purpose.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reference => 'info',
            self::HowTo => 'success',
            self::Explanation => 'warning',
        };
    }
}
