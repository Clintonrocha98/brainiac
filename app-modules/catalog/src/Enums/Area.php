<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Area: string implements HasLabel
{
    case Ti = 'ti';
    case Business = 'business';
    case Product = 'product';
    case Marketing = 'marketing';
    case Design = 'design';

    public function getLabel(): string
    {
        return __('catalog::enums.area.'.$this->value);
    }
}
