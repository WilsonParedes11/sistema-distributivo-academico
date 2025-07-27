<?php

namespace App\Filament\Resources\DistributivoAcademicoResource\Pages;

use App\Filament\Resources\DistributivoAcademicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributivoAcademico extends EditRecord
{
    protected static string $resource = DistributivoAcademicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
