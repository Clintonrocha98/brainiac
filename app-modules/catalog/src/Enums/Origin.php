<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Origin: string implements HasColor, HasLabel
{
    case Native = 'native';
    case Mirror = 'mirror';

    public function getLabel(): string
    {
        return __('catalog::enums.origin.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Native => 'primary',
            self::Mirror => 'info',
        };
    }
}
