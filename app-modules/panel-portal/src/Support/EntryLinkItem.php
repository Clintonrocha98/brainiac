<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

/**
 * Uma ligação tipada pronta para exibição, com rótulo conforme o sentido
 * (ex.: "Substitui" na saída, "Substituída por" na entrada).
 */
final readonly class EntryLinkItem
{
    public function __construct(
        public string $label,
        public string $title,
        public string $qualifiedId,
        public string $url,
    ) {}
}
