<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected static ?string $navigationLabel = 'Software de gestión de horarios';

    protected static ?string $title = 'Software de gestión de horarios';

    public function getTitle(): string
    {
        return 'Software de gestión de horarios';
    }

    public function getHeading(): string
    {
        return 'Software de gestión de horarios';
    }
}
