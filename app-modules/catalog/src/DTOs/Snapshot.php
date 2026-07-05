<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

final readonly class Snapshot
{
    /**
     * @param  array<int, SnapshotEntry>  $entries
     */
    public function __construct(
        public string $acronym,
        public array $entries,
    ) {}
}
