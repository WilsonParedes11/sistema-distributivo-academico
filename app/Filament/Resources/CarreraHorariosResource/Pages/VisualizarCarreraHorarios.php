<?php
// app/Filament/Resources/CarreraHorariosResource/Pages/VisualizarCarreraHorarios.php

namespace App\Filament\Resources\CarreraHorariosResource\Pages;

use App\Filament\Resources\CarreraHorariosResource;
use App\Models\PeriodoAcademico;
use App\Models\Horario;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VisualizarCarreraHorarios extends Page
{
    protected static string $resource = CarreraHorariosResource::class;
    protected static string $view = 'filament.resources.carrera-horarios-resource.pages.visualizar-carrera-horarios';
    protected static ?string $title = 'Horarios por Carrera';
    protected static ?string $navigationLabel = 'Ver Horarios por Carrera';

    public ?array $data = [];
    public Collection $horarios;

    public function mount(): void
    {
        $periodoActivo = PeriodoAcademico::where('activo', true)->first();

        $this->form->fill([
            'periodo_academico_id' => $periodoActivo?->id,
            'carrera_id' => null,
        ]);

        $this->horarios = collect();
        Log::info('Page mounted', ['periodo_academico_id' => $periodoActivo?->id]);
    }

    public function form(Form $form): Form
    {
        return CarreraHorariosResource::form($form);
    }

    public function consultarHorarios(): void
    {
        Log::info('consultarHorarios method called');

        $data = $this->form->getState();
        Log::info('ConsultarHorarios called with data:', ['data' => $data]);

        if (empty($data['periodo_academico_id']) || empty($data['carrera_id'])) {
            $this->horarios = collect();
            Notification::make()
                ->title('Advertencia')
                ->body('Por favor, seleccione un período académico y una carrera.')
                ->warning()
                ->send();
            Log::warning('Missing periodo_academico_id or carrera_id', ['data' => $data]);
            return;
        }

        // Log available data in Horario and DistributivoAcademico
        Log::info('Total Horarios in database:', ['count' => Horario::count()]);
        Log::info('Total Distributivos in database:', ['count' => \App\Models\DistributivoAcademico::count()]);
        Log::info('Distributivos for carrera_id and periodo_academico_id:', [
            'count' => \App\Models\DistributivoAcademico::where('carrera_id', $data['carrera_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->count()
        ]);

        $query = Horario::select('horarios.*')
            ->join('distributivo_academico', 'horarios.distributivo_academico_id', '=', 'distributivo_academico.id')
            ->where('distributivo_academico.carrera_id', $data['carrera_id'])
            ->where('distributivo_academico.periodo_academico_id', $data['periodo_academico_id'])
            ->with([
                'distributivoAcademico.asignatura',
                'distributivoAcademico.docente.user',
                'distributivoAcademico.carrera',
                'distributivoAcademico.campus'
            ]);

        Log::info('Raw SQL Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

        $this->horarios = $query->orderBy('distributivo_academico.semestre')
            ->orderBy('distributivo_academico.paralelo')
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->get();

        Log::info('Horarios retrieved:', ['count' => $this->horarios->count(), 'data' => $this->horarios->toArray()]);

        if ($this->horarios->isEmpty()) {
            Notification::make()
                ->title('Información')
                ->body('No se encontraron horarios para la carrera y período seleccionados.')
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title('Éxito')
                ->body('Se encontraron ' . $this->horarios->count() . ' horarios.')
                ->success()
                ->send();
        }
    }

    public function limpiarHorarios(): void
    {
        $this->horarios = collect();
        Log::info('Horarios cleared');
        Notification::make()
            ->title('Información')
            ->body('Horarios limpiados.')
            ->info()
            ->send();
    }

    /**
     * Obtiene los horarios organizados por día para un grupo específico
     */
    public function getHorariosPorDia($horarios): array
    {
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $horariosPorDia = [];

        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia_semana', $dia)->sortBy('hora_inicio')->values();
        }

        return $horariosPorDia;
    }

    /**
     * Obtiene los rangos de horarios disponibles según la jornada
     */
    public function getHorariosDisponibles($horarios): array
    {
        if ($horarios->isEmpty()) {
            return [];
        }

        // Determinar jornada según los horarios consultados
        $jornada = $horarios->first()->distributivoAcademico->jornada ?? 'matutina';

        $jornadaModel = \App\Models\Jornada::nombre($jornada)->first();

        if ($jornadaModel) {
            $rangos = collect($jornadaModel->intervalos)->map(function($intervalo) {
                return $intervalo['inicio'] . '-' . $intervalo['fin'];
            });

            // Filtrar rangos según horarios ocupados
            $horasOcupadas = $horarios->map(function($h) {
                return [
                    \Carbon\Carbon::parse($h->hora_inicio)->format('H:i'),
                    \Carbon\Carbon::parse($h->hora_fin)->format('H:i')
                ];
            });

            $minHora = $horasOcupadas->min(fn($h) => $h[0]) ?? null;
            $maxHora = $horasOcupadas->max(fn($h) => $h[1]) ?? null;

            return $rangos->filter(function($rango) use ($minHora, $maxHora) {
                if (!$minHora || !$maxHora) return false;
                [$inicio, $fin] = explode('-', $rango);
                return ($fin > $minHora && $inicio < $maxHora);
            })->values()->toArray();
        }

        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('consultar')
                ->label('Consultar Horarios')
                ->color('primary')
                ->icon('heroicon-o-magnifying-glass')
                ->action('consultarHorarios'),
        ];
    }
}
