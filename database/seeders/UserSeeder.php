<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Usuario administrador principal
        $admin = User::create([
            'cedula' => '0201234567',
            'nombres' => 'WILSON',
            'apellidos' => 'PAREDES',
            'email' => 'admin@libertador.edu.ec',
            'password' => Hash::make('password'),
            'tipo_usuario' => 'administrador',
            'telefono' => '0999999999',
            'activo' => true,
        ]);

        $admin->assignRole('administrador');

        // Usuario docente de ejemplo
        $docente = User::create([
            'cedula' => '0201641792',
            'nombres' => 'DIANA MAGALI',
            'apellidos' => 'ALEGRIA CAMINO',
            'email' => 'diana.alegria@libertador.edu.ec',
            'password' => Hash::make('password'),
            'tipo_usuario' => 'docente',
            'telefono' => '0987654321',
            'activo' => true,
        ]);

        $docente->assignRole('docente');

        // Usuario estudiante de ejemplo
        $estudiante = User::create([
            'cedula' => '0201111111',
            'nombres' => 'JUAN CARLOS',
            'apellidos' => 'PEREZ LOPEZ',
            'email' => 'juan.perez@estudiante.libertador.edu.ec',
            'password' => Hash::make('password'),
            'tipo_usuario' => 'estudiante',
            'telefono' => '0987654322',
            'activo' => true,
        ]);

        $estudiante->assignRole('estudiante');
    }
}
