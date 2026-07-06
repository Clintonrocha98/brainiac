<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Entry;

/**
 * Autoria de uma Entrada para exibição: dono (nativa) ou @handles (espelho).
 */
final readonly class EntryAuthorship
{
    private function __construct(
        public string $label,
        public string $names,
        public string $byLine,
    ) {}

    public static function of(Entry $entry): self
    {
        if ($entry->origin === Origin::Native) {
            $ownerName = $entry->owner->name ?? '—';

            return new self(
                label: __('panel-portal::portal.reader.owner'),
                names: $ownerName,
                byLine: __('panel-portal::portal.reader.by', ['owner' => $ownerName]),
            );
        }

        $handles = collect($entry->authors ?? [])
            ->map(static fn (string $handle): string => '@'.$handle);

        return new self(
            label: __('panel-portal::portal.reader.authors'),
            names: $handles->implode(', '),
            byLine: $handles->implode(' · '),
        );
    }
}
