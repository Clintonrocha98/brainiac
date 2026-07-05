<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\DTOs\BodyFacts;

final class DeriveBodyFacts
{
    public function execute(string $markdown): BodyFacts
    {
        $hasImage = str_contains($markdown, '![');
        $hasMermaid = (bool) preg_match('/```mermaid/i', $markdown);

        // Destinos de links markdown [texto](destino).
        preg_match_all('/\[[^\]]*\]\(([^)]+)\)/', $markdown, $matches);
        $targets = $matches[1];

        // Menções = links para outras Entradas: id qualificado (PREFIX:...) ou repo path.
        $mentions = array_values(array_filter($targets, static fn (string $t): bool => (bool) preg_match('/^[A-Z0-9]+:/', $t) || str_starts_with($t, 'repo://')));

        // Artefato: algum destino aponta para um asset HTML.
        $hasArtifact = (bool) array_filter($targets, static fn (string $t): bool => str_ends_with(strtok($t, '?#') ?: $t, '.html'));

        return new BodyFacts($hasImage, $hasMermaid, $hasArtifact, $mentions);
    }
}
