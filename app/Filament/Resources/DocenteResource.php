<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocenteResource\Pages;
use App\Filament\Resources\DocenteResource\RelationManagers;
use App\Models\Docente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class DocenteResource extends Resource
{
    protected static ?string $model = Docente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->options(User::where('tipo_usuario', 'docente')->get()->mapWithKeys(function ($user) {
                        return [$user->id => $user->nombres . ' ' . $user->apellidos];
                    }))
                    ->searchable(['nombres', 'apellidos', 'cedula'])
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('titulo_profesional')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('grado_ocupacional')
                    ->options([
                        'SP1' => 'SP1',
                        'SP2' => 'SP2',
                        'SP3' => 'SP3',
                        'SP4' => 'SP4',
                        'SP5' => 'SP5',
                        'SP6' => 'SP6',
                        'SP7' => 'SP7',
                        'SP8' => 'SP8',
                    ])
                    ->label('Grado Ocupacional')
                    ->required(),
                Forms\Components\Select::make('modalidad_trabajo')
                    ->options([
                        'MT' => 'Medio Tiempo',
                        'TC' => 'Tiempo Completo',
                    ]),
                Forms\Components\DatePicker::make('fecha_vinculacion')
                    ->required(),
                Forms\Components\Toggle::make('activo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Usuario')
                    ->searchable(['nombres', 'apellidos'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('titulo_profesional')
                    ->limit(40) // Muestra solo los primeros 40 caracteres
                    ->tooltip(fn($record) => $record->titulo_profesional) // Muestra el texto completo al pasar el mouse
                    ->searchable(),
                Tables\Columns\TextColumn::make('grado_ocupacional')
                    ->label('Grado Ocupacional')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('modalidad_trabajo'),
                Tables\Columns\TextColumn::make('fecha_vinculacion')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
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
            'index' => Pages\ListDocentes::route('/'),
            'create' => Pages\CreateDocente::route('/create'),
            'edit' => Pages\EditDocente::route('/{record}/edit'),
        ];
    }
}
