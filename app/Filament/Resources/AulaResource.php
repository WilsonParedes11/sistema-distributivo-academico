<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AulaResource\Pages;
use App\Filament\Resources\AulaResource\RelationManagers;
use App\Models\Aula;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Carrera;

class AulaResource extends Resource
{
    protected static ?string $model = Aula::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\Select::make('campus_id')
                    ->options(function () {
                        return \App\Models\Campus::all()->pluck('nombre', 'id');
                    })
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('carrera_id')
                    ->label('Carrera')
                    ->options(function (callable $get) {
                        $campusId = $get('campus_id');
                        if (!$campusId) {
                            return [];
                        }
                        return Carrera::where('campus_id', $campusId)->pluck('nombre', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->disabled(fn(callable $get) => !$get('campus_id')),
                Forms\Components\TextInput::make('edificio')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('capacidad')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'aula' => 'Aula',
                        'laboratorio' => 'Laboratorio',
                        'taller' => 'Taller',
                        'auditorio' => 'Auditorio',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('recursos_disponibles')
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
                Tables\Columns\TextColumn::make('campus.nombre')
                    ->label('Campus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('carrera.nombre')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('edificio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\IconColumn::make('activa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('recursos_disponibles')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(100),
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
            'index' => Pages\ListAulas::route('/'),
            'create' => Pages\CreateAula::route('/create'),
            'edit' => Pages\EditAula::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'campus'
            ]);
    }
}
