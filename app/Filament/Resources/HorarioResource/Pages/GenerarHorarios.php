<?php
// app/Filament/Resources/HorarioResource/Pages/GenerarHorarios.php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use App\Models\Campus;
use App\Models\PeriodoAcademico;
use App\Services\HorarioGeneratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\Carrera;

class GenerarHorarios extends Page
{
    protected static string $resource = HorarioResource::class;
    protected static string $view = 'filament.resources.horario-resource.pages.generar-horarios';
    protected static ?string $title = 'Generar Horarios Automáticamente';
    protected static ?string $navigationLabel = 'Generar Horarios';

    public ?array $data = [];

    public function mount(): void
    {
        $periodoActivo = PeriodoAcademico::where('activo', true)->first();

        $this->form->fill([
            'periodo_academico_id' => $periodoActivo?->id,
            'campus_id' => null,
            'carrera_id' => null,
            'limpiar_existentes' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Generación')
                    ->schema([
                        Forms\Components\Select::make('periodo_academico_id')
                            ->label('Período Académico')
                            ->options(PeriodoAcademico::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('campus_id')
                            ->label('Campus')
                            ->options(Campus::where('activo', true)->pluck('nombre', 'id'))
                            ->placeholder('Seleccione un campus (opcional)')
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('carrera_id', null))
                            ->helperText('Opcional: Si no selecciona un campus, se generarán horarios para todos los campus.'),

                        Forms\Components\Select::make('carrera_id')
                            ->label('Carrera')
                            ->options(function (callable $get) {
                                $campusId = $get('campus_id');
                                if (!$campusId) {
                                    // Si no hay campus seleccionado, mostrar todas las carreras
                                    return Carrera::pluck('nombre', 'id');
                                }
                                // Si hay campus seleccionado, filtrar por campus
                                return Carrera::where('campus_id', $campusId)->pluck('nombre', 'id');
                            })
                            ->placeholder('Seleccione una carrera (opcional)')
                            ->reactive()
                            ->helperText('Opcional: Si no selecciona una carrera, se generarán horarios para todas las carreras del campus seleccionado.'),

                        Forms\Components\Toggle::make('limpiar_existentes')
                            ->label('Limpiar horarios existentes antes de generar')
                            ->default(true)
                            ->helperText('Si está activado, eliminará todos los horarios existentes del período seleccionado antes de generar nuevos.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generar(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $service = new HorarioGeneratorService();

            // Usar los nuevos parámetros opcionales
            $campusId = $data['campus_id'] ?? null;
            $carreraId = $data['carrera_id'] ?? null;

            $resultado = $service->generarHorarios(
                $data['periodo_academico_id'],
                $campusId,
                $carreraId
            );

            DB::commit();

            $mensaje = "Generación completada:\n";
            $mensaje .= "✅ {$resultado['exitosos']} horarios generados exitosamente\n";

            if ($resultado['errores'] > 0) {
                $mensaje .= "❌ {$resultado['errores']} errores encontrados\n";
            }

            // Agregar información de filtros aplicados
            if ($campusId || $carreraId) {
                $mensaje .= "\n📍 Filtros aplicados:\n";
                if ($campusId) {
                    $campus = Campus::find($campusId);
                    $mensaje .= "• Campus: {$campus->nombre}\n";
                }
                if ($carreraId) {
                    $carrera = Carrera::find($carreraId);
                    $mensaje .= "• Carrera: {$carrera->nombre}\n";
                }
            }

            if (!empty($resultado['conflictos'])) {
                $mensaje .= "\n⚠️ Conflictos encontrados:\n";
                foreach (array_slice($resultado['conflictos'], 0, 5) as $conflicto) {
                    if (isset($conflicto['error_general'])) {
                        $mensaje .= "• Error general: {$conflicto['error_general']}\n";
                    } else {
                        $semestre = isset($conflicto['semestre']) ? " (Sem.{$conflicto['semestre']})" : "";
                        $paralelo = isset($conflicto['paralelo']) ? " Par.{$conflicto['paralelo']}" : "";
                        $mensaje .= "• {$conflicto['docente']} - {$conflicto['asignatura']}{$semestre}{$paralelo}: {$conflicto['razon']}\n";
                    }
                }
                if (count($resultado['conflictos']) > 5) {
                    $mensaje .= "... y " . (count($resultado['conflictos']) - 5) . " más";
                }
            }

            // Mostrar algunos mensajes de éxito si los hay
            if (!empty($resultado['mensajes']) && $resultado['exitosos'] > 0) {
                $mensaje .= "\n📋 Detalles de generación:\n";
                foreach (array_slice($resultado['mensajes'], 0, 3) as $mensajeDetalle) {
                    $mensaje .= "• {$mensajeDetalle}\n";
                }
                if (count($resultado['mensajes']) > 3) {
                    $mensaje .= "... y " . (count($resultado['mensajes']) - 3) . " más asignaciones";
                }
            }

            if ($resultado['exitosos'] > 0) {
                Notification::make()
                    ->title('Horarios generados exitosamente')
                    ->body($mensaje)
                    ->success()
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title('No se pudieron generar horarios')
                    ->body($mensaje)
                    ->warning()
                    ->persistent()
                    ->send();
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error al generar horarios')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('generar')
                ->label('Generar Horarios')
                ->color('success')
                ->icon('heroicon-o-cog-6-tooth')
                ->action('generar')
                ->requiresConfirmation()
                ->modalHeading('Confirmar generación de horarios')
                ->modalDescription(function () {
                    $data = $this->form->getState();
                    $descripcion = '¿Está seguro de que desea generar los horarios? Esta acción puede tomar varios minutos.';

                    if (isset($data['campus_id']) || isset($data['carrera_id'])) {
                        $descripcion .= '\n\nSe aplicarán los siguientes filtros:';
                        if (isset($data['campus_id']) && $data['campus_id']) {
                            $campus = Campus::find($data['campus_id']);
                            $descripcion .= '\n• Campus: ' . ($campus->nombre ?? 'No encontrado');
                        }
                        if (isset($data['carrera_id']) && $data['carrera_id']) {
                            $carrera = Carrera::find($data['carrera_id']);
                            $descripcion .= '\n• Carrera: ' . ($carrera->nombre ?? 'No encontrada');
                        }
                    } else {
                        $descripcion .= '\n\nSe generarán horarios para TODOS los campus y carreras activos.';
                    }

                    return $descripcion;
                })
                ->modalSubmitActionLabel('Sí, generar'),
        ];
    }
}
