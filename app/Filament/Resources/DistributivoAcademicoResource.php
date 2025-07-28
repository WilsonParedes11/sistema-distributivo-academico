<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistributivoAcademicoResource\Pages;
use App\Filament\Resources\DistributivoAcademicoResource\RelationManagers;
use App\Models\DistributivoAcademico;
use App\Models\PeriodoAcademico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Campus;

class DistributivoAcademicoResource extends Resource
{
    protected static ?string $model = DistributivoAcademico::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('periodo_academico_id')
                    ->label('Período Académico')
                    ->options(PeriodoAcademico::where('activo', true)->get()->mapWithKeys(function ($periodo) {
                        return [$periodo->id => $periodo->nombre . ' ' . $periodo->anio . ' ' . $periodo->periodo];
                    }))
                    ->required(),
                Forms\Components\Select::make('docente_id')
                    ->label('Docente')
                    ->options(\App\Models\Docente::all()->mapWithKeys(function ($docente) {
                        return [$docente->id => $docente->nombres . ' ' . $docente->apellidos];
                    }))
                    ->searchable(['nombres', 'apellidos', 'cedula'])
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('asignatura_id')
                    ->label('Asignatura')
                    ->options(Asignatura::all()->mapWithKeys(function ($asignatura) {
                        return [$asignatura->id => $asignatura->nombre];
                    }))
                    ->searchable(['nombre'])
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('carrera_id')
                    ->label('Carrera')
                    ->options(Carrera::all()->mapWithKeys(function ($carrera) {
                        return [$carrera->id => $carrera->nombre];
                    }))
                    ->searchable(['nombre'])
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('campus_id')
                    ->label('Campus')
                    ->options(Campus::all()->mapWithKeys(function ($campus) {
                        return [$campus->id => $campus->nombre];
                    }))
                    ->searchable(['nombre'])
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('paralelo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('semestre')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('jornada')
                    ->options([
                        'matutina' => 'Matutina',
                        'vespertina' => 'Vespertina',
                        'nocturna' => 'Nocturna',
                        'intensiva' => 'Intensiva',
                    ])
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('semestre')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
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
