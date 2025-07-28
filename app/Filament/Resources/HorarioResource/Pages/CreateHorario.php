<?php
// app/Filament/Resources/HorarioResource/Pages/CreateHorario.php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHorario extends CreateRecord
{
    protected static string $resource = HorarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
