<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Docente;
use App\Models\Estudiante;

class UserObserver
{
    public function created(User $user)
    {
        if ($user->tipo_usuario === 'docente') {
            Docente::create([
                'user_id' => $user->id,
                // Agrega aquí los campos requeridos por la tabla docentes
                'titulo_profesional' => 'Por definir',
                'grado_ocupacional' => 'SP1',
                'fecha_vinculacion' => now(),
                'activo' => true,
            ]);
        } elseif ($user->tipo_usuario === 'estudiante') {
            Estudiante::create([
                'user_id' => $user->id,
                // Agrega aquí los campos requeridos por la tabla estudiantes
                'codigo_estudiante' => 'Por definir',
                'carrera_id' => 1,
                'campus_id' => 1,
                'semestre_actual' => 1,
                'paralelo' => 'A',
                'jornada' => 'matutina',
                'fecha_ingreso' => now(),
                'estado' => 'activo',
            ]);
        }
    }
}
