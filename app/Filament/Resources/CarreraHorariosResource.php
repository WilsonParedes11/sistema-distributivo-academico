<?php
// app/Filament/Resources/CarreraHorariosResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\CarreraHorariosResource\Pages;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Carrera;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Collection;

class CarreraHorariosResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Gestión Académica';
    protected static ?string $navigationLabel = 'Horarios por Carrera';
    protected static ?string $modelLabel = 'Horario por Carrera';
    protected static ?string $pluralModelLabel = 'Horarios por Carrera';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros de Horarios')
                    ->schema([
                        Forms\Components\Select::make('periodo_academico_id')
                            ->label('Período Académico')
                            ->options(PeriodoAcademico::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($livewire) => $livewire->limpiarHorarios()),

                        Forms\Components\Select::make('carrera_id')
                            ->label('Carrera')
                            ->options(Carrera::where('activa', true)->pluck('nombre', 'id'))
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(fn($livewire) => $livewire->limpiarHorarios()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('distributivoAcademico.semestre')
                    ->label('Semestre')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distributivoAcademico.paralelo')
                    ->label('Paralelo')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distributivoAcademico.asignatura.nombre')
                    ->label('Asignatura')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('distributivoAcademico.docente.user.nombre_completo')
                    ->label('Docente')
                    ->searchable()
                    ->sortable(),

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
                    ->getStateUsing(fn($record) => $record->hora_inicio . ' - ' . $record->hora_fin),

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
                Tables\Filters\SelectFilter::make('semestre')
                    ->label('Semestre')
                    ->options([
                        1 => 'I Semestre',
                        2 => 'II Semestre',
                        3 => 'III Semestre',
                        4 => 'IV Semestre',
                        5 => 'V Semestre',
                    ]),

                Tables\Filters\SelectFilter::make('paralelo')
                    ->label('Paralelo')
                    ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D']),
            ])
            ->defaultSort('distributivoAcademico.semestre')
            ->defaultSort('distributivoAcademico.paralelo')
            ->defaultSort('dia_semana')
            ->defaultSort('hora_inicio');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\VisualizarCarreraHorarios::route('/'),
        ];
    }

    // Métodos de navegación

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }
}
