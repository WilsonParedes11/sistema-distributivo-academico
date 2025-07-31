<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Estudiante;
use App\Models\Docente;
use App\Models\Carrera;
use App\Models\Asignatura;
use App\Models\PeriodoAcademico;

class EstadisticasGeneralesWidget extends BaseWidget
{

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user instanceof \App\Models\User && method_exists($user, 'hasRole') && $user->hasRole('administrador');
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Estudiantes activos', Estudiante::activos()->count()),
            Stat::make('Docentes activos', Docente::activos()->count()),
            Stat::make('Carreras activas', Carrera::activas()->count()),
            Stat::make('Asignaturas activas', Asignatura::activas()->count()),
            Stat::make('Periodo académico vigente', PeriodoAcademico::periodoActivo()?->nombre ?? 'N/A'),
        ];
    }
}
