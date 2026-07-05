<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Audience: string implements HasLabel
{
    case Ti = 'ti';
    case Business = 'business';
    case Product = 'product';
    case Marketing = 'marketing';
    case Design = 'design';
    case All = 'all';
    case External = 'external';

    public function getLabel(): string
    {
        return __("catalog::enums.audience.{$this->value}");
    }
}
