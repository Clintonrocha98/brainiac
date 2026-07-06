<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum Format: string implements HasIcon, HasLabel
{
    case Readme = 'readme';
    case Context = 'context';
    case Architecture = 'architecture';
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';
    case Adr = 'adr';
    case Spec = 'spec';
    case Plan = 'plan';
    case Prd = 'prd';

    public function getLabel(): string
    {
        return __('catalog::enums.format.'.$this->value);
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Readme => Heroicon::OutlinedBookOpen,
            self::Context => Heroicon::OutlinedMap,
            self::Architecture => Heroicon::OutlinedCubeTransparent,
            self::Reference => Heroicon::OutlinedTableCells,
            self::HowTo => Heroicon::OutlinedListBullet,
            self::Explanation => Heroicon::OutlinedLightBulb,
            self::Adr => Heroicon::OutlinedScale,
            self::Spec => Heroicon::OutlinedDocumentText,
            self::Plan => Heroicon::OutlinedClipboardDocumentList,
            self::Prd => Heroicon::OutlinedFlag,
        };
    }

    public function isPrd(): bool
    {
        return $this === self::Prd;
    }
}
