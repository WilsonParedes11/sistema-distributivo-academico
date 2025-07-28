<?php
// app/Filament/Resources/HorarioResource/Pages/VisualizarHorarios.php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use App\Models\Campus;
use App\Models\Carrera;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Services\HorarioGeneratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class VisualizarHorarios extends Page
{
    protected static string $resource = HorarioResource::class;
    protected static string $view = 'filament.resources.horario-resource.pages.visualizar-horarios';
    protected static ?string $title = 'Visualizar Horarios';
    protected static ?string $navigationLabel = 'Ver Horarios';

    public ?array $data = [];
    public Collection $horarios;
    public string $tipoVista = 'carrera';

    public function mount(): void
    {
        $periodoActivo = PeriodoAcademico::where('activo', true)->first();

        $this->form->fill([
            'periodo_academico_id' => $periodoActivo?->id,
            'tipo_vista' => 'carrera',
        ]);

        $this->horarios = collect();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros de Visualización')
                    ->schema([
                        Forms\Components\Select::make('periodo_academico_id')
                            ->label('Período Académico')
                            ->options(PeriodoAcademico::pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                        Forms\Components\Select::make('tipo_vista')
                            ->label('Tipo de Vista')
                            ->options([
                                'carrera' => 'Por Carrera/Curso',
                                'docente' => 'Por Docente',
                                'aula' => 'Por Aula',
                                'campus' => 'Por Campus',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->tipoVista = $state;
                                $this->limpiarFormulario();
                                $this->limpiarHorarios();
                            }),

                        // Campos condicionales según el tipo de vista
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('campus_id')
                                ->label('Campus')
                                ->options(Campus::where('activo', true)->pluck('nombre', 'id'))
                                ->required()
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => in_array($get('tipo_vista'), ['carrera', 'aula', 'campus']))
                                ->afterStateUpdated(fn() => $this->limpiarSeleccionesAdicionales()),

                            Forms\Components\Select::make('carrera_id')
                                ->label('Carrera')
                                ->options(function (Forms\Get $get) {
                                    $campusId = $get('campus_id');
                                    if (!$campusId)
                                        return [];

                                    return Campus::find($campusId)
                                        ->carreras()
                                        ->where('activa', true)
                                        ->pluck('nombre', 'id');
                                })
                                ->required()
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
                                ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                            Forms\Components\Select::make('semestre')
                                ->label('Semestre')
                                ->options([
                                    1 => 'I Semestre',
                                    2 => 'II Semestre',
                                    3 => 'III Semestre',
                                    4 => 'IV Semestre',
                                    5 => 'V Semestre',
                                ])
                                ->required()
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
                                ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                            Forms\Components\TextInput::make('paralelo')
                                ->label('Paralelo')
                                ->placeholder('Ej: A, B, C')
                                ->maxLength(2)
                                ->required()
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
                                ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                            Forms\Components\Select::make('docente_id')
                                ->label('Docente')
                                ->options(
                                    Docente::with('user')
                                        ->where('activo', true)
                                        ->get()
                                        ->pluck('user.nombre_completo', 'id')
                                )
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'docente')
                                ->afterStateUpdated(fn() => $this->limpiarHorarios()),
                        ]),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function consultarHorarios(): void
    {
        $data = $this->form->getState();

        if (!$data['periodo_academico_id']) {
            $this->horarios = collect();
            return;
        }

        $service = new HorarioGeneratorService();

        $this->horarios = match ($data['tipo_vista']) {
            'carrera' => $this->consultarHorarioCarrera($service, $data),
            'docente' => $this->consultarHorarioDocente($service, $data),
            default => collect(),
        };
    }

    private function consultarHorarioCarrera(HorarioGeneratorService $service, array $data): Collection
    {
        if (!isset($data['carrera_id'], $data['semestre'], $data['paralelo'], $data['campus_id'])) {
            return collect();
        }

        return $service->obtenerHorarioCarrera(
            $data['carrera_id'],
            $data['semestre'],
            strtoupper($data['paralelo']),
            $data['campus_id'],
            $data['periodo_academico_id']
        );
    }

    private function consultarHorarioDocente(HorarioGeneratorService $service, array $data): Collection
    {
        if (!isset($data['docente_id'])) {
            return collect();
        }

        return $service->obtenerHorarioDocente(
            $data['docente_id'],
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
        return [
            '07:00-09:00',
            '09:00-11:00',
            '11:00-13:00',
            '14:00-16:00',
            '16:00-18:00',
            '18:00-20:00',
            '19:00-21:00',
            '21:00-23:00'
        ];
    }

    private function limpiarHorarios(): void
    {
        $this->horarios = collect();
    }

    private function limpiarFormulario(): void
    {
        $data = $this->form->getState();
        unset($data['carrera_id'], $data['semestre'], $data['paralelo'], $data['docente_id'], $data['campus_id']);
        $this->form->fill($data);
    }

    private function limpiarSeleccionesAdicionales(): void
    {
        $data = $this->form->getState();
        unset($data['carrera_id']);
        $this->form->fill($data);
        $this->limpiarHorarios();
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
