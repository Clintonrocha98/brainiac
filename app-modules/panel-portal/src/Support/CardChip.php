<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

/**
 * Chip exibido no rodapé de um card de contexto (cores do Filament).
 */
final readonly class CardChip
{
    public function __construct(
        public string $label,
        public string $color,
    ) {}
}
