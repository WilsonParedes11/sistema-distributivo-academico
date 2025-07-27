<?php

namespace App\Filament\Resources\DistributivoAcademicoResource\Pages;

use App\Filament\Resources\DistributivoAcademicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributivoAcademicos extends ListRecords
{
    protected static string $resource = DistributivoAcademicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
