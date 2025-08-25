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
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

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
                            ->options(PeriodoAcademico::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                        Forms\Components\Select::make('tipo_vista')
                            ->label('Tipo de Vista')
                            ->options([
                                'carrera' => 'Por Carrera/Curso',
                                'docente' => 'Por Docente',
                                // 'aula' => 'Por Aula',
                                // 'campus' => 'Por Campus',
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
                                ->required(fn(Forms\Get $get) => in_array($get('tipo_vista'), ['carrera', 'aula', 'campus']))
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
                                        ->where('carreras.activa', true)
                                        ->pluck('nombre', 'carreras.id');
                                })
                                ->required(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
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
                                ->required(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
                                ->afterStateUpdated(fn() => $this->limpiarHorarios()),

                            Forms\Components\TextInput::make('paralelo')
                                ->label('Paralelo')
                                ->placeholder('Ej: A, B, C')
                                ->maxLength(2)
                                ->required(fn(Forms\Get $get) => $get('tipo_vista') === 'carrera')
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
                                ->required(fn(Forms\Get $get) => $get('tipo_vista') === 'docente')
                                ->reactive()
                                ->visible(fn(Forms\Get $get) => $get('tipo_vista') === 'docente')
                                ->afterStateUpdated(function(Forms\Get $get) {
                                    $this->limpiarHorarios();
                                    if($get('tipo_vista') === 'docente' && $get('periodo_academico_id')) {
                                        $this->consultarHorarios();
                                    }
                                }),
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
        $data = $this->form->getState();
        // Determinar jornada según los horarios consultados
        $jornada = null;
        if ($this->horarios->isNotEmpty()) {
            $jornada = $this->horarios->first()->distributivoAcademico->jornada ?? null;
        }
        // Si no hay horarios, intentar obtener jornada desde los filtros
        if (!$jornada && isset($data['jornada'])) {
            $jornada = $data['jornada'];
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

    private function limpiarHorarios(): void
    {
        $this->horarios = collect();
    }

    private function limpiarFormulario(): void
    {
        $data = $this->form->getState();
        // Limpiar campos según la vista seleccionada
        if ($this->tipoVista === 'carrera') {
            // Vista carrera no necesita docente
            unset($data['docente_id']);
        }

        if ($this->tipoVista === 'docente') {
            // Vista docente no necesita filtros de carrera/curso
            unset($data['carrera_id'], $data['semestre'], $data['paralelo'], $data['campus_id']);
        }

        // Para otras vistas futuras se podría añadir lógica similar
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

            Forms\Components\Actions\Action::make('imprimir')
                ->label('Imprimir')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->visible(fn() => $this->horarios->isNotEmpty())
                ->action('imprimirHorario'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir Horario')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn() => $this->horarios->isNotEmpty())
                ->action('imprimirHorario')
                ->openUrlInNewTab(),
        ];
    }

    public function imprimirHorario()
    {
        if ($this->horarios->isEmpty()) {
            $this->notify('warning', 'No hay horarios para imprimir');
            return;
        }

        $data = $this->form->getState();
        $horariosPorDia = $this->getHorariosPorDia();
        $horariosDisponibles = $this->getHorariosDisponibles();

        // Obtener información adicional para el PDF
        $titulo = $this->obtenerTituloHorario($data);
        $subtitulo = $this->obtenerSubtituloHorario($data);

        // Filtrar rangos horarios
        $horasOcupadas = $this->horarios->map(function ($h) {
            return [
                \Carbon\Carbon::parse($h->hora_inicio)->format('H:i'),
                \Carbon\Carbon::parse($h->hora_fin)->format('H:i')
            ];
        });

        $minHora = $horasOcupadas->min(fn($h) => $h[0]) ?? null;
        $maxHora = $horasOcupadas->max(fn($h) => $h[1]) ?? null;

        $rangosFiltrados = collect($horariosDisponibles)->filter(function ($rango) use ($minHora, $maxHora) {
            if (!$minHora || !$maxHora)
                return false;
            [$inicio, $fin] = explode('-', $rango);
            return ($fin > $minHora && $inicio < $maxHora);
        });

        $pdf = Pdf::loadView('horarios.imprimir', [
            'horarios' => $this->horarios,
            'horariosPorDia' => $horariosPorDia,
            'rangosFiltrados' => $rangosFiltrados,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'tipoVista' => $this->tipoVista,
            'data' => $data,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            'horario-' . strtolower(str_replace(' ', '-', $titulo)) . '.pdf'
        );
    }

    private function obtenerTituloHorario(array $data): string
    {
        return match ($this->tipoVista) {
            'carrera' => $data['carrera_id']
            ? Carrera::find($data['carrera_id'])->nombre . ' - ' . $data['semestre'] . $data['paralelo']
            : 'Horario por Carrera',
            'docente' => $data['docente_id']
            ? Docente::find($data['docente_id'])->user->nombre_completo
            : 'Horario por Docente',
            'aula' => 'Horario por Aula',
            'campus' => 'Horario por Campus',
            default => 'Horario Académico'
        };
    }

    private function obtenerSubtituloHorario(array $data): string
    {
        $periodo = $data['periodo_academico_id']
            ? PeriodoAcademico::find($data['periodo_academico_id'])->nombre
            : '';

        $campus = '';
        if (isset($data['campus_id']) && $data['campus_id']) {
            $campus = ' - ' . Campus::find($data['campus_id'])->nombre;
        }

        return $periodo . $campus;
    }
}
