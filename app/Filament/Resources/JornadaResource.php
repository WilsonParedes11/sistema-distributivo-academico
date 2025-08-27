<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JornadaResource\Pages;
use App\Filament\Resources\JornadaResource\RelationManagers;
use App\Models\Jornada;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JornadaResource extends Resource
{
    protected static ?string $model = Jornada::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    // protected static ?string $navigationGroup = 'Configuración Académica';

    protected static ?string $modelLabel = 'Jornada';

    protected static ?string $pluralModelLabel = 'Jornadas';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Jornada')
                    ->schema([
                        Forms\Components\Select::make('nombre')
                            ->label('Nombre de la Jornada')
                            ->options([
                                'matutina' => 'Matutina',
                                'vespertina' => 'Vespertina',
                                'nocturna' => 'Nocturna',
                            ])
                            ->required()
                            ->placeholder('Seleccione el tipo de jornada'),

                        Forms\Components\TimePicker::make('hora_inicio')
                            ->label('Hora de Inicio')
                            ->required()
                            ->seconds(false)
                            ->helperText('Hora en que inicia la jornada académica'),

                        Forms\Components\TextInput::make('cantidad_horas')
                            ->label('Cantidad de Períodos')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(15)
                            ->step(1)
                            ->suffix('períodos')
                            ->helperText('Número total de períodos académicos en la jornada'),

                        Forms\Components\TextInput::make('duracion_hora')
                            ->label('Duración del Período')
                            ->required()
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(120)
                            ->step(5)
                            ->suffix('minutos')
                            ->default(50)
                            ->helperText('Duración en minutos de cada período académico'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración de Receso Académico')
                    ->schema([
                        Forms\Components\TimePicker::make('hora_inicio_receso')
                            ->label('Hora de Inicio del Receso')
                            ->seconds(false)
                            ->helperText('Hora en que inicia el receso académico (opcional)'),

                        Forms\Components\TimePicker::make('hora_fin_receso')
                            ->label('Hora de Fin del Receso')
                            ->seconds(false)
                            ->helperText('Hora en que termina el receso académico (opcional)')
                            ->requiredWith('hora_inicio_receso'),
                    ])
                    ->columns(2)
                    ->description('Configure el horario de receso si la jornada lo requiere. Durante este tiempo no se programarán clases.'),

                Forms\Components\Section::make('Vista Previa de Horarios')
                    ->schema([
                        Forms\Components\Placeholder::make('preview')
                            ->label('')
                            ->content(function ($get) {
                                $nombre = $get('nombre') ?? 'Sin definir';
                                $horaInicio = $get('hora_inicio');
                                $cantidadHoras = $get('cantidad_horas') ?? 0;
                                $duracionHora = $get('duracion_hora') ?? 50;
                                $horaInicioReceso = $get('hora_inicio_receso');
                                $horaFinReceso = $get('hora_fin_receso');

                                if (!$horaInicio || !$cantidadHoras) {
                                    return 'Complete los campos para ver la vista previa de horarios.';
                                }

                                $preview = "Jornada: " . ucfirst($nombre) . "\n\n";
                                $inicio = \Carbon\Carbon::parse($horaInicio);
                                $receso_inicio = $horaInicioReceso ? \Carbon\Carbon::parse($horaInicioReceso) : null;
                                $receso_fin = $horaFinReceso ? \Carbon\Carbon::parse($horaFinReceso) : null;

                                $periodosGenerados = 0;
                                $periodo = 1;
                                $recesoMostrado = false;

                                while ($periodosGenerados < $cantidadHoras) {
                                    $fin = $inicio->copy()->addMinutes($duracionHora);

                                    // Verificar si el período actual intersecta con el receso
                                    if ($receso_inicio && $receso_fin) {
                                        // Si el período empieza antes del receso y termina después de que empiece el receso
                                        if ($inicio->lt($receso_inicio) && $fin->gt($receso_inicio)) {
                                            // Crear período hasta el inicio del receso
                                            $preview .= "Período $periodo: {$inicio->format('H:i')} - {$receso_inicio->format('H:i')}\n";
                                            $periodosGenerados++;
                                            $periodo++;

                                            // Mostrar receso
                                            if (!$recesoMostrado) {
                                                $preview .= "RECESO: {$receso_inicio->format('H:i')} - {$receso_fin->format('H:i')}\n";
                                                $recesoMostrado = true;
                                            }

                                            // Continuar después del receso
                                            $inicio = $receso_fin->copy();
                                            continue;
                                        }
                                        // Si el período está completamente dentro del receso, saltar al final del receso
                                        elseif ($inicio->gte($receso_inicio) && $fin->lte($receso_fin)) {
                                            if (!$recesoMostrado) {
                                                $preview .= "RECESO: {$receso_inicio->format('H:i')} - {$receso_fin->format('H:i')}\n";
                                                $recesoMostrado = true;
                                            }
                                            $inicio = $receso_fin->copy();
                                            continue;
                                        }
                                        // Si el período empieza durante el receso, mover al final del receso
                                        elseif ($inicio->gte($receso_inicio) && $inicio->lt($receso_fin)) {
                                            if (!$recesoMostrado) {
                                                $preview .= "RECESO: {$receso_inicio->format('H:i')} - {$receso_fin->format('H:i')}\n";
                                                $recesoMostrado = true;
                                            }
                                            $inicio = $receso_fin->copy();
                                            continue;
                                        }
                                    }

                                    // Período normal (no intersecta con receso)
                                    $preview .= "Período $periodo: {$inicio->format('H:i')} - {$fin->format('H:i')}\n";
                                    $periodosGenerados++;
                                    $periodo++;
                                    $inicio = $fin;
                                }

                                // Agregar horario completo
                                $ultimaHora = $inicio->format('H:i');
                                $horaInicioJornada = \Carbon\Carbon::parse($horaInicio)->format('H:i');
                                $preview .= "\nHorario completo: {$horaInicioJornada} - {$ultimaHora}";

                                return $preview;
                            })
                            ->extraAttributes(['class' => 'whitespace-pre-line font-mono text-sm']),
                    ])
                    ->visible(fn ($get) => $get('hora_inicio') && $get('cantidad_horas')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Jornada')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'matutina' => 'success',
                        'vespertina' => 'warning',
                        'nocturna' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('hora_inicio')
                    ->label('Hora de Inicio')
                    ->formatStateUsing(fn (string $state): string => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad_horas')
                    ->label('Períodos')
                    ->numeric()
                    ->sortable()
                    ->suffix(' períodos'),

                Tables\Columns\TextColumn::make('duracion_hora')
                    ->label('Duración')
                    ->numeric()
                    ->sortable()
                    ->suffix(' min'),

                Tables\Columns\TextColumn::make('receso')
                    ->label('Receso Académico')
                    ->getStateUsing(function ($record) {
                        if ($record->hora_inicio_receso && $record->hora_fin_receso) {
                            $inicio = \Carbon\Carbon::parse($record->hora_inicio_receso)->format('H:i');
                            $fin = \Carbon\Carbon::parse($record->hora_fin_receso)->format('H:i');
                            return $inicio . ' - ' . $fin;
                        }
                        return 'Sin receso';
                    })
                    ->badge()
                    ->color(fn ($record) => $record->hora_inicio_receso ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('horario_completo')
                    ->label('Horario Completo')
                    ->getStateUsing(function ($record) {
                        $inicio = \Carbon\Carbon::parse($record->hora_inicio);
                        $horaFin = $record->hora_fin_jornada;

                        return $inicio->format('H:i') . ' - ' . $horaFin->format('H:i');
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nombre')
                    ->label('Tipo de Jornada')
                    ->options([
                        'matutina' => 'Matutina',
                        'vespertina' => 'Vespertina',
                        'nocturna' => 'Nocturna',
                    ]),

                Tables\Filters\TernaryFilter::make('tiene_receso')
                    ->label('Con Receso')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('hora_inicio_receso'),
                        false: fn (Builder $query) => $query->whereNull('hora_inicio_receso'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hora_inicio');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJornadas::route('/'),
            'create' => Pages\CreateJornada::route('/create'),
            'edit' => Pages\EditJornada::route('/{record}/edit'),
        ];
    }
}
