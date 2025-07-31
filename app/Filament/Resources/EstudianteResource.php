<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstudianteResource\Pages;
use App\Filament\Resources\EstudianteResource\RelationManagers;
use App\Models\Campus;
use App\Models\Estudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Carrera;

class EstudianteResource extends Resource
{
    protected static ?string $model = Estudiante::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->options(User::where('tipo_usuario', 'estudiante')->get()->mapWithKeys(function ($user) {
                        return [$user->id => $user->nombres . ' ' . $user->apellidos];
                    }))
                    ->searchable(['nombres', 'apellidos', 'cedula'])
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('codigo_estudiante')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('carrera_id')
                    ->options(Carrera::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->label('Carrera')
                    ->required(),
                Forms\Components\Select::make('campus_id')
                    ->options(Campus::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()
                    ->label('Campus')
                    ->required(),
                Forms\Components\TextInput::make('semestre_actual')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('paralelo')
                    ->required()
                    ->maxLength(2),
                Forms\Components\Select::make('jornada')
                    ->options([
                        'matutina' => 'Matutina',
                        'vespertina' => 'Vespertina',
                        'nocturna' => 'Nocturna',
                        'intensiva' => 'Intensiva',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->required(),
                Forms\Components\Select::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'retirado' => 'Retirado',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Estudiante')
                    ->searchable(['nombres', 'apellidos'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo_estudiante')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carrera.nombre')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campus.nombre')
                    ->label('Campus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semestre_actual')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paralelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jornada'),
                Tables\Columns\TextColumn::make('fecha_ingreso')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListEstudiantes::route('/'),
            'create' => Pages\CreateEstudiante::route('/create'),
            'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
    }
}
