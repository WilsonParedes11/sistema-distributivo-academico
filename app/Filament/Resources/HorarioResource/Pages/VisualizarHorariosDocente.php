<?php
// app/Filament/Resources/HorarioResource/Pages/VisualizarHorariosDocente.php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Services\HorarioGeneratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class VisualizarHorariosDocente extends Page
{
    protected static string $resource = HorarioResource::class;
    protected static string $view = 'filament.resources.horario-resource.pages.visualizar-horarios-docente';
    protected static ?string $title = 'Mis Horarios';
    protected static ?string $navigationLabel = 'Mis Horarios';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public ?array $data = [];
    public Collection $horarios;

    public function mount(): void
    {
        $periodoActivo = PeriodoAcademico::where('activo', true)->first();
        $docente = Docente::where('user_id', Auth::id())->first();

        if (!$docente) {
            $this->horarios = collect();
            return;
        }

        $this->form->fill([
            'periodo_academico_id' => $periodoActivo?->id,
        ]);

        $this->horarios = collect();
        if ($periodoActivo && $docente) {
            $this->consultarHorarios();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros')
                    ->schema([
                        Forms\Components\Select::make('periodo_academico_id')
                            ->label('Período Académico')
                            ->options(PeriodoAcademico::pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->consultarHorarios()),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function consultarHorarios(): void
    {
        $data = $this->form->getState();
        $docente = Docente::where('user_id', Auth::id())->first();

        if (!$data['periodo_academico_id'] || !$docente) {
            $this->horarios = collect();
            return;
        }

        $service = new HorarioGeneratorService();
        $this->horarios = $service->obtenerHorarioDocente(
            $docente->id,
            $data['periodo_academico_id']
        );
    }

    public function getHorariosPorDia(): array
    {
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $horariosPorDia = [];

        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $this->horarios
                ->where('dia_semana', $dia)
                ->sortBy('hora_inicio')
                ->values();
        }

        return $horariosPorDia;
    }

    public function getHorariosDisponibles(): array
    {
        // Determinar la jornada del docente según los horarios consultados
        $jornada = null;
        if ($this->horarios->isNotEmpty()) {
            $jornada = $this->horarios->first()->distributivoAcademico->jornada ?? null;
        }
        // Si no hay horarios, intentar obtener jornada desde el primer distributivo del docente
        if (!$jornada) {
            $docente = \App\Models\Docente::where('user_id', \Auth::id())->first();
            if ($docente) {
                $distributivo = $docente->distributivos()->first();
                $jornada = $distributivo?->jornada;
            }
        }
        if (!$jornada) {
            // Por defecto matutina
            $jornada = 'matutina';
        }
        $jornadaModel = \App\Models\Jornada::nombre($jornada)->first();
        if ($jornadaModel) {
            return collect($jornadaModel->intervalos)->map(function($intervalo) {
                return $intervalo['inicio'] . '-' . $intervalo['fin'];
            })->toArray();
        }
        // Fallback: lista vacía
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

    protected function getHeaderActions(): array
    {
        return [];
    }
}
