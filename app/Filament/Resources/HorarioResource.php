<?php
// app/Filament/Resources/HorarioResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\HorarioResource\Pages;
use App\Models\Horario;
use App\Models\Campus;
use App\Models\Carrera;
use App\Models\PeriodoAcademico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HorarioResource extends Resource
{
    protected static ?string $model = Horario::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Gestión Académica';
    protected static ?string $navigationLabel = 'Horarios';
    protected static ?string $modelLabel = 'Horario';
    protected static ?string $pluralModelLabel = 'Horarios';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Horario')
                    ->schema([
                        Forms\Components\Select::make('distributivo_academico_id')
                            ->label('Distributivo Académico')
                            ->relationship('distributivoAcademico')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return $record->docente->user->nombre_completo . ' - ' .
                                    $record->asignatura->nombre . ' (' .
                                    $record->carrera->codigo . '-' . $record->semestre . $record->paralelo . ')';
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('dia_semana')
                            ->label('Día de la Semana')
                            ->options([
                                'lunes' => 'Lunes',
                                'martes' => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves' => 'Jueves',
                                'viernes' => 'Viernes',
                                'sabado' => 'Sábado',
                            ])
                            ->required(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('hora_inicio')
                                    ->label('Hora de Inicio')
                                    ->required()
                                    ->seconds(false),

                                Forms\Components\TimePicker::make('hora_fin')
                                    ->label('Hora de Fin')
                                    ->required()
                                    ->seconds(false)
                                    ->after('hora_inicio'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('aula')
                                    ->label('Aula')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('edificio')
                                    ->label('Edificio')
                                    ->maxLength(50),
                            ]),

                        Forms\Components\Select::make('tipo_clase')
                            ->label('Tipo de Clase')
                            ->options([
                                'teorica' => 'Teórica',
                                'practica' => 'Práctica',
                                'laboratorio' => 'Laboratorio',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('distributivoAcademico.docente.user.nombre_completo')
                    ->label('Docente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distributivoAcademico.asignatura.nombre')
                    ->label('Asignatura')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('distributivoAcademico.carrera.codigo')
                    ->label('Carrera')
                    ->badge(),

                Tables\Columns\TextColumn::make('distributivo_info')
                    ->label('Curso')
                    ->getStateUsing(function ($record) {
                        return $record->distributivoAcademico->semestre . $record->distributivoAcademico->paralelo;
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('dia_semana')
                    ->label('Día')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'lunes' => 'primary',
                        'martes' => 'success',
                        'miercoles' => 'warning',
                        'jueves' => 'danger',
                        'viernes' => 'info',
                        'sabado' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('horario')
                    ->label('Horario')
                    ->getStateUsing(function ($record) {
                        return $record->hora_inicio . ' - ' . $record->hora_fin;
                    }),

                Tables\Columns\TextColumn::make('aula')
                    ->label('Aula')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tipo_clase')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'teorica' => 'primary',
                        'practica' => 'success',
                        'laboratorio' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('distributivoAcademico.campus.nombre')
                    ->label('Campus')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('campus')
                    ->relationship('distributivoAcademico.campus', 'nombre')
                    ->label('Campus'),

                Tables\Filters\SelectFilter::make('carrera')
                    ->relationship('distributivoAcademico.carrera', 'nombre')
                    ->label('Carrera'),

                Tables\Filters\SelectFilter::make('dia_semana')
                    ->label('Día de la Semana')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sabado' => 'Sábado',
                    ]),

                Tables\Filters\SelectFilter::make('tipo_clase')
                    ->label('Tipo de Clase')
                    ->options([
                        'teorica' => 'Teórica',
                        'practica' => 'Práctica',
                        'laboratorio' => 'Laboratorio',
                    ]),
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
            ->defaultSort('dia_semana')
            ->defaultSort('hora_inicio');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHorarios::route('/'),
            'create' => Pages\CreateHorario::route('/create'),
            'edit' => Pages\EditHorario::route('/{record}/edit'),
            'generar' => Pages\GenerarHorarios::route('/generar'),
            'visualizar' => Pages\VisualizarHorarios::route('/visualizar'),
            'mis-horarios' => Pages\VisualizarHorariosDocente::route('/mis-horarios'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $periodoActivo = PeriodoAcademico::where('activo', true)->first();
        if (!$periodoActivo)
            return null;

        return static::getModel()::whereHas('distributivoAcademico', function ($query) use ($periodoActivo) {
            $query->where('periodo_academico_id', $periodoActivo->id);
        })->count();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->hasPermissionTo('ver_horarios');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermissionTo('ver_horarios');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('ver_horarios');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('gestionar_horarios');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('gestionar_horarios');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('gestionar_horarios');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasPermissionTo('gestionar_horarios');
    }
}

