<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar los seeders en orden específico debido a las dependencias
        $this->call([
                // 1. Primero los roles y permisos (necesarios para usuarios)
            RolesAndPermissionsSeeder::class,

                // 2. Crear campus (independiente)
            CampusSeeder::class,

                // 3. Crear carreras y asociarlas con campus
            CarreraSeeder::class,

                // 4. Crear asignaturas (dependen de carreras)
            AsignaturaSeeder::class,

                // 5. Crear períodos académicos (independiente)
            PeriodoAcademicoSeeder::class,

                // 6. Crear usuarios base (después de roles y permisos)
            UserSeeder::class,
        ]);

        $this->command->info('🎉 Base de datos sembrada exitosamente!');
        $this->command->info('📊 Datos creados:');
        $this->command->info('   - Roles y permisos del sistema');
        $this->command->info('   - 5 Campus del instituto');
        $this->command->info('   - 13 Carreras técnicas y tecnológicas');
        $this->command->info('   - Asignaturas por carrera y semestre');
        $this->command->info('   - Períodos académicos (2023-2026)');
        $this->command->info('   - Usuarios base del sistema');
        $this->command->info('');
        $this->command->info('🔑 Credenciales de acceso:');
        $this->command->info('   Admin Principal: admin@libertador.edu.ec / password');
        $this->command->info('   Admin Prueba: test@example.com / password');
        $this->command->info('   Docente: diana.alegria@libertador.edu.ec / password');
        $this->command->info('   Estudiante: juan.perez@estudiante.libertador.edu.ec / password');
        $this->command->info('');
        $this->command->info('🌐 Panel de administración: /admin');
    }
}
