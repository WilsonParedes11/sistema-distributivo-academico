<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Docente;

class HorasDocentesWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $user = \Filament\Facades\Filament::auth()->user();
        return $user instanceof \App\Models\User && method_exists($user, 'hasRole') && $user->hasRole('administrador');
    }
    protected function getStats(): array
    {
        $docentes = Docente::activos()->get();
        $totalHoras = $docentes->sum(fn($d) => $d->totalHorasActuales());
        $promedioHoras = $docentes->count() ? $totalHoras / $docentes->count() : 0;

        return [
            Stat::make('Total horas docentes', $totalHoras),
            Stat::make('Promedio horas por docente', round($promedioHoras, 2)),
        ];
    }
}
