<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $pluralLabel = 'Usuarios';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('administrador');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cedula')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->label('Cédula'),
                Forms\Components\TextInput::make('nombres')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombres'),
                Forms\Components\TextInput::make('apellidos')
                    ->required()
                    ->maxLength(255)
                    ->label('Apellidos'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Email'),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verificado'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->hiddenOn('edit')
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->label('Contraseña'),
                Forms\Components\Select::make('tipo_usuario')
                    ->required()
                    ->options([
                        'administrador' => 'Administrador',
                        'docente' => 'Docente',
                        'estudiante' => 'Estudiante',
                    ])
                    ->label('Tipo de Usuario'),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->maxLength(255)
                    ->label('Teléfono'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellidos')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_usuario'),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
