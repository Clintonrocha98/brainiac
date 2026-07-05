<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum PrdVersionState: string implements HasLabel
{
    case Draft = 'draft';
    case Frozen = 'frozen';

    public function getLabel(): string
    {
        return __("catalog::enums.prd_version_state.{$this->value}");
    }
}
