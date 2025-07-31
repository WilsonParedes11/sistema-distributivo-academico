<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Estudiante;
use App\Models\Carrera;

class ProgresoEstudiantesWidget extends ChartWidget
{
    public static function canView(): bool
    {
        $user = \Filament\Facades\Filament::auth()->user();
        return $user instanceof \App\Models\User && method_exists($user, 'hasRole') && $user->hasRole('administrador');
    }
    protected static ?string $heading = 'Progreso de Estudiantes por Carrera';
    protected function getData(): array
    {
        $carreras = Carrera::activas()->get();
        $labels = $carreras->pluck('nombre')->toArray();
        $data = $carreras->map(function ($carrera) {
            return Estudiante::activos()->where('carrera_id', $carrera->id)
                ->avg('semestre_actual') ?? 0;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Semestre promedio',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
    protected function getType(): string
    {
        return 'bar';
    }
}
