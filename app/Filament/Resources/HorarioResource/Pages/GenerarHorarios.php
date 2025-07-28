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

                        Forms\Components\CheckboxList::make('campus_ids')
                            ->label('Campus (dejar vacío para todos)')
                            ->options(Campus::where('activo', true)->pluck('nombre', 'id'))
                            ->columns(2),

                        Forms\Components\Toggle::make('limpiar_existentes')
                            ->label('Limpiar horarios existentes antes de generar')
                            ->default(true)
                            ->helperText('Si está activado, eliminará todos los horarios existentes del período seleccionado antes de generar nuevos.'),

                        Forms\Components\Placeholder::make('info')
                            ->label('Información')
                            ->content('
                                <div class="text-sm text-gray-600">
                                    <h4 class="font-semibold mb-2">Reglas de generación:</h4>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Un docente no puede dictar más de 2 horas el mismo día</li>
                                        <li>Las horas del mismo día deben ser consecutivas</li>
                                        <li>Se respetan las jornadas definidas en el distributivo</li>
                                        <li>Se asignan aulas disponibles automáticamente</li>
                                        <li>Se evitan conflictos de horarios entre docentes y aulas</li>
                                    </ul>
                                </div>
                            '),
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
