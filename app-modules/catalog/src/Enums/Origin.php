<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Origin: string implements HasLabel
{
    case Native = 'native';
    case Mirror = 'mirror';

    public function getLabel(): string
    {
        return __('catalog::enums.origin.'.$this->value);
    }
}
