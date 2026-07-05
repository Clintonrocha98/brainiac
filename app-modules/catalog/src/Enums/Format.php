<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Format: string implements HasLabel
{
    case Readme = 'readme';
    case Context = 'context';
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';
    case Adr = 'adr';
    case Spec = 'spec';
    case Plan = 'plan';
    case Prd = 'prd';

    public function getLabel(): string
    {
        return __("catalog::enums.format.{$this->value}");
    }

    public function isPrd(): bool
    {
        return $this === self::Prd;
    }
}
