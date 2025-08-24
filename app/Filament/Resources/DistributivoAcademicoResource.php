<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributivoAcademicoResource\Pages;
use App\Models\DistributivoAcademico;
use App\Models\PeriodoAcademico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Campus;
use App\Models\Aula;
use App\Models\Jornada;

class DistributivoAcademicoResource extends Resource
{
    protected static ?string $model = DistributivoAcademico::class;

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('periodo_academico_id')
                    ->label('Período Académico')
                    ->options(PeriodoAcademico::where('activo', true)->get()->mapWithKeys(function ($periodo) {
                        return [$periodo->id => $periodo->nombre . ' ' . $periodo->anio . ' ' . $periodo->periodo];
                    }))
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('campus_id')
                    ->label('Campus')
                    ->options(fn() => Campus::all()->pluck('nombre', 'id'))
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('carrera_id')
                    ->label('Carrera')
                    ->options(function (callable $get) {
                        $campusId = $get('campus_id');
                        if (!$campusId)
                            return [];
                        return Carrera::where('campus_id', $campusId)->pluck('nombre', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->disabled(fn(callable $get) => !$get('campus_id')),
                Forms\Components\Select::make('asignatura_id')
                    ->label('Asignatura')
                    ->options(function (callable $get) {
                        $carreraId = $get('carrera_id');
                        if (!$carreraId)
                            return [];
                        return Asignatura::where('carrera_id', $carreraId)->pluck('nombre', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->disabled(fn(callable $get) => !$get('carrera_id')),
                Forms\Components\Select::make('docente_id')
                    ->label('Docente')
                    ->options(\App\Models\Docente::all()->mapWithKeys(function ($docente) {
                        return [$docente->id => $docente->nombres . ' ' . $docente->apellidos];
                    }))
                    ->searchable(['nombres', 'apellidos', 'cedula'])
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('semestre')
                    ->label('Semestre')
                    ->options([
                        1 => 'I',
                        2 => 'II',
                        3 => 'III',
                        4 => 'IV',
                        5 => 'V',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('paralelo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('jornada')
                    ->label('Jornada')
                    ->options(fn() => Jornada::all()->pluck('nombre', 'nombre')->map(fn($nombre) => ucfirst($nombre)))
                    ->required(),
                Forms\Components\TextInput::make('horas_componente_practico')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('horas_clase_semana')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('horas_actividades_docencia')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('horas_investigacion_semanal')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('nombre_proyecto_investigacion')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('horas_direccion_academica_semanal')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('detalle_horas_direccion')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('total_horas_semanales')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('observaciones')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodoAcademico.nombre')
                    ->label('Período Académico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('docente.user.full_name')
                    ->label('Docente')
                    ->searchable(['nombres', 'apellidos'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('asignatura.nombre')
                    ->label('Asignatura')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('carrera.nombre')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campus.nombre')
                    ->label('Campus')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('paralelo')
                    ->label('Paralelo (Aula)')
                    ->formatStateUsing(function ($state) {
                        $aula = Aula::where('codigo', $state)->first();
                        if ($aula) {
                            return $aula->codigo . ' - ' . ($aula->nombre ?? $aula->paralelo ?? '');
                        }
                        return $state;
                    })
                    ->toggleable(true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('semestre')
                    ->label('Semestre')
                    ->formatStateUsing(function ($state) {
                        $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X'];
                        return $romanos[$state] ?? $state;
                    })
                    ->toggleable(true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('jornada')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('horas_componente_practico')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horas_clase_semana')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horas_actividades_docencia')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horas_investigacion_semanal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_proyecto_investigacion')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('horas_direccion_academica_semanal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalle_horas_direccion')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_horas_semanales')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListDistributivoAcademicos::route('/'),
            'create' => Pages\CreateDistributivoAcademico::route('/create'),
            'edit' => Pages\EditDistributivoAcademico::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'periodoAcademico',
                'docente.user',
                'asignatura',
                'carrera',
                'campus'
            ]);
    }
}
