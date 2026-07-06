<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Pilha de versões de um PRD ordenada da mais recente para a mais antiga.
 * Resolve a versão selecionada a partir do rótulo pedido na URL (?v=v2.0);
 * sem rótulo (ou rótulo inválido), a mais recente vence.
 */
final readonly class PrdVersionStack
{
    /**
     * @param  EloquentCollection<int, PrdVersion>  $versions
     */
    private function __construct(
        public EloquentCollection $versions,
        public ?PrdVersion $selected,
    ) {}

    public static function of(Entry $entry, ?string $requestedLabel): self
    {
        $versions = $entry->prdVersions
            ->sortBy([['major', 'desc'], ['minor', 'desc']])
            ->values();

        $requested = $requestedLabel !== null
            ? $versions->first(static fn (PrdVersion $version): bool => self::labelOf($version) === $requestedLabel)
            : null;

        return new self($versions, $requested ?? $versions->first());
    }

    public static function labelOf(PrdVersion $version): string
    {
        return sprintf('v%d.%d', $version->major ?? 0, $version->minor ?? 0);
    }

    public function isEmpty(): bool
    {
        return $this->versions->isEmpty();
    }

    public function selectedLabel(): ?string
    {
        return $this->selected instanceof PrdVersion ? self::labelOf($this->selected) : null;
    }

    public function latestLabel(): ?string
    {
        $latest = $this->versions->first();

        return $latest instanceof PrdVersion ? self::labelOf($latest) : null;
    }

    public function isReadingOldVersion(): bool
    {
        $latest = $this->versions->first();

        return $this->selected instanceof PrdVersion
            && $latest instanceof PrdVersion
            && $this->selected->isNot($latest);
    }

    public function bodyMarkdown(): ?string
    {
        return $this->selected?->body_markdown;
    }

    /**
     * @return array<int, PrdVersionOption>
     */
    public function options(): array
    {
        return $this->versions
            ->map(fn (PrdVersion $version): PrdVersionOption => new PrdVersionOption(
                value: self::labelOf($version),
                state: $version->state,
                meta: DisplayDate::short($version->frozen_at) ?? __('panel-portal::portal.prd.editing'),
                isSelected: $this->selected instanceof PrdVersion && $version->is($this->selected),
            ))
            ->all();
    }
}
