<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Widgets\CustomAccountUserWidget;
use App\Filament\Widgets\CustomAccountWidget;
use Filament\Actions\Action;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->userMenuItems([
                'logout' => fn(Action $action) => $action
                    ->url(route('dashboard'))
                    ->postToUrl(false),
            ])
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                CustomAccountUserWidget::class,
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
            ->plugins([
                MobileBottomNav::make(),
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): HtmlString => new HtmlString('
                <style>
                    @media (max-width: 1023px) {
                        /* 1. Perbaikan Topbar (Header Utama) */
                        .fi-topbar {
                            padding-top: calc(env(safe-area-inset-top, 0px) + 50px) !important;
                            height: auto !important;
                        }

                        /* Menyesuaikan posisi konten utama agar tidak tertutup header */
                        .fi-main {
                            margin-top: 50px !important;
                        }

                        /* 2. Perbaikan Sidebar / Title saat Menu di-Tap */
                        .fi-sidebar {
                            /* Memberikan padding atas pada container sidebar */
                            padding-top: calc(env(safe-area-inset-top, 0px) + 50px) !important;
                        }

                        /* Opsional: Menurunkan posisi tombol Close (X) di dalam sidebar mobile jika ada */
                        .fi-sidebar-close-btn {
                            top: calc(env(safe-area-inset-top, 0px) + 28px) !important;
                        }
                    }
                </style>
            '),
            );
    }
}
