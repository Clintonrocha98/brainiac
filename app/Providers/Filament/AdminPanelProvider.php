<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Enums\FilamentPanel;
use App\Enums\NavigationGroup;
use App\Filament\Plugins\KnowledgeBase\BetterKnowledgeBasePlugin;
use App\Filament\Shared\Pages\LoginPage;
use Basement\BetterMails\Filament\FilamentBetterEmailPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup as BaseNavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBaseCompanionPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Http\Middleware\SetUserLocale;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

final class AdminPanelProvider extends PanelProvider
{
    private FilamentPanel $panelId = FilamentPanel::Admin;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->path($this->panelId->value)
            ->id($this->panelId->value)
            ->login(LoginPage::class)
            ->topbar()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Purple,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'info' => Color::Indigo,
                'gray' => Color::Gray,
            ])
            ->viteTheme(sprintf('resources/css/filament/%s/theme.css', $this->panelId->value))
            ->defaultThemeMode(config('sycorax.filament.theme_mode'))
            ->discoverResources(in: modules_path('panel-admin/src/Filament/Resources'), for: 'He4rt\\Admin\\Filament\\Resources')
            ->discoverPages(in: modules_path('panel-admin/src/Filament/Pages'), for: 'He4rt\\Admin\\Filament\\Pages')
            ->discoverWidgets(in: modules_path('panel-admin/src/Filament/Widgets'), for: 'He4rt\\Admin\\Filament\\Widgets')
            ->discoverClusters(in: modules_path('panel-admin/src/Filament/Clusters'), for: 'He4rt\\Admin\\Filament\\Clusters')
            ->topbar(condition: false)
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups(collect(NavigationGroup::navigation())
                ->map(fn ($group) => BaseNavigationGroup::make()->label($group->getLabel()))
                ->toArray())
            ->plugins([
                BetterKnowledgeBasePlugin::make(),
                KnowledgeBaseCompanionPlugin::make()
                    ->helpMenuRenderHook(PanelsRenderHook::SIDEBAR_FOOTER)
                    ->knowledgeBasePanelId('admin')
                    ->disableKnowledgeBasePanelButton()
                    ->modalPreviews()
                    ->slideOverPreviews(),
                FilamentBetterEmailPlugin::make(),
                ActivityLogPlugin::make()
                    ->label('Log')
                    ->pluralLabel('Logs')
                    ->navigationGroup(NavigationGroup::System),
                FilamentEditProfilePlugin::make()
                    ->slug('profile')
                    ->setTitle(__('My Profile'))
                    ->setNavigationLabel(__('My Profile'))
                    ->setNavigationGroup(__('Group Profile'))
                    ->shouldRegisterNavigation(value: false)
                    ->shouldShowDeleteAccountForm(value: false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowLocaleForm(
                        options: [
                            'en' => 'English',
                            'pt_BR' => 'Português',
                        ],
                    )
                    ->shouldShowThemeColorForm(),
            ])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn (): string => __('My Profile'))
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('fas-user-pen'),
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
