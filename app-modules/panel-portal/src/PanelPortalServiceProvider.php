<?php

declare(strict_types=1);

namespace He4rt\Portal;

use He4rt\Portal\Livewire\AreasIndex;
use He4rt\Portal\Livewire\CollectionsIndex;
use He4rt\Portal\Livewire\GlobalSearch;
use He4rt\Portal\Livewire\ProjectsIndex;
use He4rt\Portal\Livewire\ShowContext;
use He4rt\Portal\Livewire\ShowEntry;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PanelPortalServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'panel-portal');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-portal');
        $this->loadRoutesFrom(__DIR__.'/../routes/portal-routes.php');

        Livewire::component('panel-portal.projects-index', ProjectsIndex::class);
        Livewire::component('panel-portal.areas-index', AreasIndex::class);
        Livewire::component('panel-portal.collections-index', CollectionsIndex::class);
        Livewire::component('panel-portal.show-context', ShowContext::class);
        Livewire::component('panel-portal.show-entry', ShowEntry::class);
        Livewire::component('panel-portal.global-search', GlobalSearch::class);
    }
}
