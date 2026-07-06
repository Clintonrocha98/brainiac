<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Filament\Shared\Pages\LoginPage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use He4rt\Portal\Filament\Search\EntryGlobalSearchProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\Http\Middleware\SetUserLocale;

/**
 * Painel de LEITURA do catálogo (portal de docs). Autoria e administração
 * vivem no painel admin; aqui todo usuário autenticado navega e lê.
 */
final class PortalPanelProvider extends PanelProvider
{
    private FilamentPanel $panelId = FilamentPanel::Portal;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->path($this->panelId->value)
            ->id($this->panelId->value)
            ->login(LoginPage::class)
            ->brandName('Brainiac')
            ->viteTheme('resources/css/filament/portal/theme.css')
            ->defaultThemeMode(config('brainiac.filament.theme_mode'))
            ->discoverPages(in: modules_path('panel-portal/src/Filament/Pages'), for: 'He4rt\\Portal\\Filament\\Pages')
            ->globalSearch(EntryGlobalSearchProvider::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->topNavigation()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SetUserLocale::class,
            ]);
    }
}
