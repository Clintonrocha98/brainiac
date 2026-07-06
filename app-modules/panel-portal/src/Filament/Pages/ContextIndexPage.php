<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use Filament\Pages\Page;
use He4rt\Portal\Support\ContextCard;

/**
 * Base dos índices de contexto (Projetos, Áreas e Trilhas): um grid de cards
 * que leva à visão geral de cada contexto.
 */
abstract class ContextIndexPage extends Page
{
    protected string $view = 'panel-portal::filament.pages.context-index';

    /**
     * @return array<int, ContextCard>
     */
    abstract protected function cards(): array;

    /**
     * @return string|array<int, string>
     */
    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return static::getRouteName().'*';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'cards' => $this->cards(),
        ];
    }
}
