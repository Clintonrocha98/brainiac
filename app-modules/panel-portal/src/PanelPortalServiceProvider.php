<?php

declare(strict_types=1);

namespace He4rt\Portal;

use Illuminate\Support\ServiceProvider;

class PanelPortalServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'panel-portal');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-portal');
    }
}
