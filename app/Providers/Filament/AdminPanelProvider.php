<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use Filament\FontProviders\GoogleFontProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->favicon(asset('storage/favicon/icon1.ico'))
            ->brandName('Tirta Perwira')
            //->brandLogo(asset('storage/favicon/icon.ico'))
            ->darkMode(true)
            ->font('Poppins', provider: GoogleFontProvider::class)
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'primary' => Color::Emerald,
                'success' => Color::Teal,
                'warning' => Color::Amber,
            ])
            ->navigationGroups([
                'Operations',
                'Finance',
                'Vehicles & Fuel',
                'User Management',
                'System',
            ])
            ->navigationItems([
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                    ->url(fn(): string => Pages\Dashboard::getUrl()),
                NavigationItem::make('Buku Petunjuk')
                    ->icon('heroicon-o-book-open')
                    ->url('https://docs.google.com/document/d/1n7amiLpSnTVTaP7xKra06lxuRgPnKrifU0bWs92z0Ew/edit?usp=sharing', shouldOpenInNewTab: true),
                NavigationItem::make('Doc Dev')
                    ->icon('heroicon-o-code-bracket-square')
                    ->url('https://docs.google.com/document/d/1n7amiLpSnTVTaP7xKra06lxuRgPnKrifU0bWs92z0Ew/edit?usp=sharing', shouldOpenInNewTab: true)
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
            ])
            ->maxContentWidth('full')
            ->breadcrumbs()
            ->collapsibleNavigationGroups()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Finance overview section
                \App\Filament\Widgets\TransactionStatsWidget::class,

                // Charts section
                \App\Filament\Widgets\FuelTypeDonutWidget::class,

                // Tables section
                \App\Filament\Widgets\LatestBalancesWidget::class,
                \App\Filament\Widgets\LatestTransactionsWidget::class,
                \App\Filament\Widgets\TopVehiclesWidget::class,

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->renderHook(
                'panels::footer',
                fn() => view('components.filament.footer')
            )
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ]);
    }
}
