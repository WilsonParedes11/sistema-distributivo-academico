<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';

    protected static ?string $title = 'Inicio';

    public function getTitle(): string
    {
        return 'Inicio';
    }
}
