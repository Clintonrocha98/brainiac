<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

/**
 * Card dos índices de contexto (Projetos, Áreas e Trilhas).
 */
final readonly class ContextCard
{
    /**
     * @param  array<int, CardChip>  $chips
     */
    public function __construct(
        public string $badge,
        public string $title,
        public string $description,
        public string $meta,
        public string $url,
        public array $chips = [],
    ) {}
}
