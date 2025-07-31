<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsignaturaResource\Pages;
use App\Filament\Resources\AsignaturaResource\RelationManagers;
use App\Models\Asignatura;
use App\Models\Carrera;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AsignaturaResource extends Resource
{
    protected static ?string $model = Asignatura::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('carrera_id')
                    ->label('Carrera')
                    ->options(Carrera::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->preload()->required(),
                // Forms\Components\TextInput::make('semestre')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('horas_semanales')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('horas_practicas')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                Forms\Components\TextInput::make('creditos')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('descripcion')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activa')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('carrera.nombre')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('semestre')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('horas_semanales')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('horas_practicas')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('creditos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activa')
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
            'index' => Pages\ListAsignaturas::route('/'),
            'create' => Pages\CreateAsignatura::route('/create'),
            'edit' => Pages\EditAsignatura::route('/{record}/edit'),
        ];
    }
}
