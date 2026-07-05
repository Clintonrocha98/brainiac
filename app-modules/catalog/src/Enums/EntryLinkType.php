<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum EntryLinkType: string implements HasLabel
{
    case Supersedes = 'supersedes';
    case Related = 'related';
    case DependsOn = 'depends_on';
    case PartOf = 'part_of';

    public function getLabel(): string
    {
        return __('catalog::enums.entry_link_type.'.$this->value);
    }
}
