<?php
// app/Filament/Resources/HorarioResource/Pages/ListHorarios.php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHorarios extends ListRecords
{
    protected static string $resource = HorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar')
                ->label('Generar Horarios')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('success')
                ->url(fn() => static::getResource()::getUrl('generar')),

            Actions\Action::make('visualizar')
                ->label('Ver Horarios')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->url(fn() => static::getResource()::getUrl('visualizar')),

            Actions\CreateAction::make(),
        ];
    }
}
