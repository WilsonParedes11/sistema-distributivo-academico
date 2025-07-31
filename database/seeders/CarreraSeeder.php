<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;
use App\Models\Campus;

class CarreraSeeder extends Seeder
{
    public function run()
    {
        $carreras = [
            [
                'nombre' => 'DESARROLLO DE SOFTWARE',
                'codigo' => 'DS',
                'tipo' => 'tecnica',
                'duracion_semestres' => 4,
                'descripcion' => 'Carrera técnica enfocada en el desarrollo de aplicaciones y sistemas de software',
                'activa' => true,
                'campus_codigo' => '23ABRIL'
            ],
            [
                'nombre' => 'RIEGO Y PRODUCCIÓN AGRÍCOLA',
                'codigo' => 'RPA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en sistemas de riego y producción agrícola sostenible',
                'activa' => true,
                'campus_codigo' => 'MATRIZ'
            ],
            [
                'nombre' => 'PRODUCCIÓN PECUARIA',
                'codigo' => 'PP',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en producción y manejo de animales de granja',
                'activa' => true,
                'campus_codigo' => 'MATRIZ'
            ],
            [
                'nombre' => 'MECÁNICA AUTOMOTRIZ',
                'codigo' => 'MA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en diagnóstico, mantenimiento y reparación de vehículos',
                'activa' => true,
                'campus_codigo' => 'GUARANDA'
            ],
            [
                'nombre' => 'ELECTRICIDAD',
                'codigo' => 'ELE',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en instalaciones y sistemas eléctricos',
                'activa' => true,
                'campus_codigo' => 'GUARANDA'
            ],
            [
                'nombre' => 'ELECTRÓNICA',
                'codigo' => 'ELEC',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en sistemas electrónicos y control',
                'activa' => true,
                'campus_codigo' => 'GUARANDA'
            ],
            [
                'nombre' => 'EDUCACIÓN INICIAL',
                'codigo' => 'EI',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en educación y desarrollo infantil',
                'activa' => true,
                'campus_codigo' => 'GUARANDA'
            ],
            [
                'nombre' => 'TECNOLOGÍA SUPERIOR EN DESARROLLO INFANTIL INTEGRAL',
                'codigo' => 'TSDII',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en atención integral al desarrollo infantil',
                'activa' => true,
                'campus_codigo' => 'GUARANDA'
            ],
            [
                'nombre' => 'TECNOLOGÍA SUPERIOR EN ADMINISTRACIÓN',
                'codigo' => 'TSA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en administración de empresas y organizaciones',
                'activa' => true,
                'campus_codigo' => 'SHIMIATUK'
            ],
            [
                'nombre' => 'ADMINISTRACIÓN',
                'codigo' => 'ADM',
                'tipo' => 'tecnica',
                'duracion_semestres' => 4,
                'descripcion' => 'Carrera técnica en administración empresarial',
                'activa' => true,
                'campus_codigo' => 'SHIMIATUK'
            ],
            [
                'nombre' => 'CONTABILIDAD',
                'codigo' => 'CONT',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en contabilidad y auditoría',
                'activa' => true,
                'campus_codigo' => 'PUYAHUATA'
            ],
            [
                'nombre' => 'CENTRO DE IDIOMAS',
                'codigo' => 'CI',
                'tipo' => 'tecnica',
                'duracion_semestres' => 2,
                'descripcion' => 'Programa de capacitación en idiomas extranjeros',
                'activa' => true,
                'campus_codigo' => '23ABRIL'
            ]
        ];

        foreach ($carreras as $data) {
            $campus = Campus::where('codigo', $data['campus_codigo'])->first();
            if ($campus) {
                Carrera::create([
                    'nombre' => $data['nombre'],
                    'codigo' => $data['codigo'],
                    'tipo' => $data['tipo'],
                    'duracion_semestres' => $data['duracion_semestres'],
                    'descripcion' => $data['descripcion'],
                    'activa' => $data['activa'],
                    'campus_id' => $campus->id,
                ]);
            } else {
                $this->command->warn("Campus no encontrado: " . $data['campus_codigo'] . " para la carrera " . $data['nombre']);
            }
        }

        $this->command->info('Carreras creadas exitosamente con campus_id.');
    }
}
