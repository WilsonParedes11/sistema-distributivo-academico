<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
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
use App\Filament\Resources\HorarioResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(asset('img/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->profile()
            ->resources([
                HorarioResource::class,
                // Otros recursos...
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                \App\Filament\Widgets\EstadisticasGeneralesWidget::class,
                // \App\Filament\Widgets\HorasDocentesWidget::class,
                \App\Filament\Widgets\ProgresoEstudiantesWidget::class,
                // \App\Filament\Widgets\AsignaturasPorCarreraWidget::class,
            ])
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label(fn(): string => \Filament\Facades\Filament::auth()->user()->nombres ?? 'Profile'),
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
            ->navigationItems([
                // Otros ítems de navegación...
                \Filament\Navigation\NavigationItem::make('Mis Horarios')
                    ->icon('heroicon-o-calendar')
                    ->url(
                        fn() =>
                        \Filament\Facades\Filament::auth()->user()?->tipo_usuario === 'docente'
                            ? HorarioResource::getUrl('mis-horarios')
                            : (\Filament\Facades\Filament::auth()->user()?->tipo_usuario === 'estudiante'
                                ? HorarioResource::getUrl('mis-horarios-estudiante')
                                : '#')
                    )
                    ->visible(fn() => in_array(\Filament\Facades\Filament::auth()->user()?->tipo_usuario, ['docente', 'estudiante'])),
            ]);
    }
}
