<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;

/**
 * Uma Entrada dentro de um snapshot de federação (a "largura do cano"): o
 * conjunto de metadados que um repo de TI empurra por doc. Campos opcionais do
 * contrato ganham default na borda via {@see self::fromPayload()}.
 */
final readonly class SnapshotEntry
{
    /**
     * @param  array<int, string>  $authors  handles do git (git config user.name) que criaram/editaram
     * @param  array<int, Audience>  $audience  áreas que devem descobrir o doc (default do contrato: [department])
     * @param  array<int, string>  $keywords  texto livre para busca
     * @param  array<int, string>  $projectAcronyms  siglas de outros projetos-assunto (a origem entra sozinha na faceta)
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
        public array $audience,
        public array $keywords,
        public Status $status,
        public array $projectAcronyms,
    ) {}

    /**
     * Mapeia uma entry crua do payload do webhook para o DTO, aplicando os
     * defaults do contrato de federação aos campos opcionais ausentes:
     * `audience` → `[department]`, `status` → `published`, `keywords`/`projects` → `[]`.
     *
     * @param  array<string, mixed>  $entry
     */
    public static function fromPayload(array $entry): self
    {
        $department = Area::from((string) $entry['department']);

        return new self(
            qualifiedId: (string) $entry['qualified_id'],
            nativeId: (string) $entry['native_id'],
            title: (string) $entry['title'],
            summary: (string) $entry['summary'],
            purpose: Purpose::from((string) $entry['purpose']),
            format: Format::from((string) $entry['format']),
            department: $department,
            bodyMarkdown: (string) $entry['body_markdown'],
            gitPointer: isset($entry['git_pointer']) ? (string) $entry['git_pointer'] : null,
            authors: self::strings($entry['authors'] ?? []),
            audience: self::audience($entry['audience'] ?? [], $department),
            keywords: self::strings($entry['keywords'] ?? []),
            status: isset($entry['status']) ? Status::from((string) $entry['status']) : Status::Published,
            projectAcronyms: self::strings($entry['projects'] ?? []),
        );
    }

    /**
     * Áreas que devem descobrir o doc; ausente ou vazio cai no default do
     * contrato: apenas o departamento dono (toda Area é uma Audience válida).
     *
     * @return array<int, Audience>
     */
    private static function audience(mixed $value, Area $department): array
    {
        $values = self::strings($value);

        if ($values === []) {
            return [Audience::from($department->value)];
        }

        return array_map(Audience::from(...), $values);
    }

    /**
     * Normaliza um valor cru do payload numa lista de strings não vazias.
     *
     * @return array<int, string>
     */
    private static function strings(mixed $value): array
    {
        return array_values(array_filter(
            array_map(strval(...), (array) $value),
            static fn (string $item): bool => $item !== '',
        ));
    }
}
