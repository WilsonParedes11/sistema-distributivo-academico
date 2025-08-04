<?php

namespace App\Filament\Resources\JornadaResource\Pages;

use App\Filament\Resources\JornadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJornada extends EditRecord
{
    protected static string $resource = JornadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
