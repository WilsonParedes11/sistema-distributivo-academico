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

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

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
                Forms\Components\TextInput::make('hora_inicio')
                    ->required(),
                Forms\Components\TextInput::make('cantidad_horas')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('duracion_hora')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hora_inicio'),
                Tables\Columns\TextColumn::make('cantidad_horas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duracion_hora')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListJornadas::route('/'),
            'create' => Pages\CreateJornada::route('/create'),
            'edit' => Pages\EditJornada::route('/{record}/edit'),
        ];
    }
}
