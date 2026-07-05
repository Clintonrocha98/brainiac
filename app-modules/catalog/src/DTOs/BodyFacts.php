<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

final readonly class BodyFacts
{
    /**
     * @param  array<int, string>  $mentions  ids/paths citados no corpo
     */
    public function __construct(
        public bool $hasImage,
        public bool $hasMermaid,
        public bool $hasArtifact,
        public array $mentions,
    ) {}

    /**
     * @return array{has_image: bool, has_mermaid: bool, has_artifact: bool, mentions: array<int, string>}
     */
    public function toColumns(): array
    {
        return [
            'has_image' => $this->hasImage,
            'has_mermaid' => $this->hasMermaid,
            'has_artifact' => $this->hasArtifact,
            'mentions' => $this->mentions,
        ];
    }
}
