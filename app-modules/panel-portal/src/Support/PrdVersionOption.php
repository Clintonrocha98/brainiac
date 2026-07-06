<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Enums\PrdVersionState;

/**
 * Uma opção do seletor de versões do PRD (dropdown e rail lateral).
 */
final readonly class PrdVersionOption
{
    public function __construct(
        public string $value,
        public PrdVersionState $state,
        public string $meta,
        public bool $isSelected,
    ) {}
}
