<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Carrera;

class AsignaturasPorCarreraWidget extends ChartWidget
{
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user instanceof \App\Models\User && method_exists($user, 'hasRole') && $user->hasRole('administrador');
    }

    protected static ?string $heading = 'Asignaturas por Carrera';

    protected int | string | array $columnSpan = 6;

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $carreras = Carrera::activas()->get();
        $labels = $carreras->pluck('nombre')->toArray();
        $data = $carreras->map(fn($c) => $c->asignaturasActivas()->count())->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Asignaturas',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
