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
            'campus_ids' => [],
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
                            ->options(PeriodoAcademico::pluck('nombre', 'id'))
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('campus_id')
                            ->label('Campus')
                            ->options(Campus::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('carrera_id', null)),

                        Forms\Components\Select::make('carrera_id')
                            ->label('Carrera')
                            ->options(function (callable $get) {
                                $campusId = $get('campus_id');
                                if (!$campusId) {
                                    return [];
                                }
                                // Si la relación es uno a muchos:
                                return Carrera::where('campus_id', $campusId)->pluck('nombre', 'id');
                                // Si la relación es muchos a muchos:
                                // return \App\Models\Campus::find($campusId)?->carreras()->pluck('nombre', 'id') ?? [];
                            })
                            ->required()
                            ->disabled(fn(callable $get) => !$get('campus_id'))
                            ->reactive(),

                        Forms\Components\Toggle::make('limpiar_existentes')
                            ->label('Limpiar horarios existentes antes de generar')
                            ->default(true)
                            ->helperText('Si está activado, eliminará todos los horarios existentes del período seleccionado antes de generar nuevos.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generar(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $service = new HorarioGeneratorService();
            $resultado = $service->generarHorarios(
                $data['periodo_academico_id'],
                $data['campus_ids'] ?? []
            );

            DB::commit();

            $mensaje = "Generación completada:\n";
            $mensaje .= "✅ {$resultado['exitosos']} horarios generados exitosamente\n";

            if ($resultado['errores'] > 0) {
                $mensaje .= "❌ {$resultado['errores']} errores encontrados\n";
            }

            if (!empty($resultado['conflictos'])) {
                $mensaje .= "\nConflictos encontrados:\n";
                foreach (array_slice($resultado['conflictos'], 0, 5) as $conflicto) {
                    $mensaje .= "• {$conflicto['docente']} - {$conflicto['asignatura']}: {$conflicto['razon']}\n";
                }
                if (count($resultado['conflictos']) > 5) {
                    $mensaje .= "... y " . (count($resultado['conflictos']) - 5) . " más";
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
                ->modalDescription('¿Está seguro de que desea generar los horarios? Esta acción puede tomar varios minutos.')
                ->modalSubmitActionLabel('Sí, generar'),
        ];
    }
}
