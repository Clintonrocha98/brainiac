<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Collection as CatalogCollection;
use He4rt\Portal\Support\ContextCard;
use He4rt\Portal\Support\ContextType;
use He4rt\Portal\Support\Markdown;
use He4rt\Portal\Support\PortalContext;

/**
 * Visão geral de um contexto: lista de documentos, barra do repositório
 * (projetos federados), introdução da trilha e trilhas da área.
 */
abstract class ShowContextPage extends PortalContextPage
{
    protected string $view = 'panel-portal::filament.pages.show-context';

    public function getTitle(): string
    {
        return $this->context()->name();
    }

    public function getSubheading(): ?string
    {
        return $this->subtitle($this->context());
    }

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        $context = $this->context();

        return [
            $context->indexUrl() => $context->typeLabel(),
            $context->name(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $context = $this->context();
        $flat = $context->flatEntries($this->contextEntries());

        return [
            'context' => $context,
            'flat' => $flat,
            'listLabel' => $context->type === ContextType::Collection
                ? __('panel-portal::portal.overview.trail_label', ['count' => $flat->count()])
                : __('panel-portal::portal.overview.docs_label', ['count' => $flat->count()]),
            'introHtml' => $this->introHtml($context),
            'trailCards' => $this->trailCards(),
        ];
    }

    private function subtitle(PortalContext $context): string
    {
        return match ($context->type) {
            ContextType::Project => (string) $this->project?->technical_name,
            ContextType::Area => __('panel-portal::portal.overview.area_subtitle', ['area' => (string) $this->area?->getLabel()]),
            ContextType::Collection => (string) $this->collection?->summary,
        };
    }

    private function introHtml(PortalContext $context): ?string
    {
        if ($context->type !== ContextType::Collection || $this->collection?->body_markdown === null) {
            return null;
        }

        return resolve(Markdown::class)->toHtml($this->collection->body_markdown);
    }

    /**
     * Trilhas destinadas à Área do contexto, como mini-cards.
     *
     * @return array<int, ContextCard>
     */
    private function trailCards(): array
    {
        if (!$this->area instanceof Area) {
            return [];
        }

        return CatalogCollection::query()
            ->visibleToArea($this->area)
            ->withCount('entries')
            ->orderBy('title')
            ->get()
            ->map(static fn (CatalogCollection $trail): ContextCard => new ContextCard(
                badge: '»',
                title: $trail->title,
                description: $trail->summary,
                meta: __('panel-portal::portal.overview.trail_meta', ['count' => $trail->entries_count]),
                url: ShowCollection::getUrl(['collection' => $trail]),
            ))
            ->all();
    }
}
