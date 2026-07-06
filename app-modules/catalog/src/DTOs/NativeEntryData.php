<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

use BackedEnum;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;

/**
 * Dados de uma Entrada nativa vindos da autoria (criação e edição).
 */
final readonly class NativeEntryData
{
    /**
     * @param  array<int, Audience>  $audience
     * @param  array<int, string>  $keywords
     * @param  array<int, string>  $subjectProjectIds
     */
    public function __construct(
        public string $nativeId,
        public string $title,
        public string $summary,
        public Purpose $purpose,
        public Format $format,
        public Area $department,
        public array $audience,
        public array $keywords,
        public Status $status,
        public string $ownerId,
        public array $subjectProjectIds = [],
        public ?string $bodyMarkdown = null,
    ) {}

    /**
     * O estado do form pode trazer enums já hidratados (casts do model) ou
     * valores crus (string) — os dois formatos são aceitos.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromForm(array $data): self
    {
        $bodyMarkdown = $data['body_markdown'] ?? null;

        return new self(
            nativeId: (string) $data['native_id'],
            title: (string) $data['title'],
            summary: (string) $data['summary'],
            purpose: self::enum(Purpose::class, $data['purpose']),
            format: self::enum(Format::class, $data['format']),
            department: self::enum(Area::class, $data['department']),
            audience: array_map(
                static fn (mixed $audience): Audience => self::enum(Audience::class, $audience),
                array_values((array) ($data['audience'] ?? [])),
            ),
            keywords: array_values(array_filter(
                (array) ($data['keywords'] ?? []),
                static fn (mixed $keyword): bool => is_string($keyword) && $keyword !== '',
            )),
            status: self::enum(Status::class, $data['status']),
            ownerId: (string) $data['owner_id'],
            subjectProjectIds: array_values((array) ($data['subject_project_ids'] ?? [])),
            bodyMarkdown: is_string($bodyMarkdown) && mb_trim($bodyMarkdown) !== '' ? $bodyMarkdown : null,
        );
    }

    /**
     * @template TEnum of BackedEnum
     *
     * @param  class-string<TEnum>  $enum
     * @return TEnum
     */
    private static function enum(string $enum, mixed $value): BackedEnum
    {
        return $value instanceof $enum ? $value : $enum::from((string) $value);
    }
}
