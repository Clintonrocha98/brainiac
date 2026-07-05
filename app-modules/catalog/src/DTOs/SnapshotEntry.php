<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;

final readonly class SnapshotEntry
{
    /**
     * @param  array<int, string>  $authors  handles do git (git config user.name) que criaram/editaram
     */
    public function __construct(
        public string $qualifiedId,
        public string $nativeId,
        public string $title,
        public string $summary,
        public Purpose $purpose,
        public Format $format,
        public Area $department,
        public string $bodyMarkdown,
        public ?string $gitPointer,
        public array $authors,
    ) {}
}
