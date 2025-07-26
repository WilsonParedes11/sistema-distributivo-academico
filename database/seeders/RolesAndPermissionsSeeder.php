<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
        $permissions = [
            'gestionar_usuarios',
            'gestionar_campus',
            'gestionar_carreras',
            'gestionar_asignaturas',
            'gestionar_docentes',
            'gestionar_estudiantes',
            'gestionar_distributivo',
            'gestionar_horarios',
            'gestionar_aulas',
            'gestionar_periodos',
            'ver_horarios',
            'ver_distributivo',
            'ver_reportes',
            'exportar_datos',
            'importar_datos',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $administrador = Role::create(['name' => 'administrador']);
        $docente = Role::create(['name' => 'docente']);
        $estudiante = Role::create(['name' => 'estudiante']);

        // Asignar permisos a roles
        $administrador->givePermissionTo(Permission::all());
        $docente->givePermissionTo(['ver_horarios', 'ver_distributivo']);
        $estudiante->givePermissionTo(['ver_horarios']);
    }
}
