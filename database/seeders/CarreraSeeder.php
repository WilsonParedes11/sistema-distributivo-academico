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
                'campus' => ['23ABRIL'] // Campus donde se imparte
            ],
            [
                'nombre' => 'TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE',
                'codigo' => 'TSDS',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Carrera tecnológica superior en desarrollo de software con énfasis en tecnologías avanzadas',
                'activa' => true,
                'campus' => ['23ABRIL']
            ],
            [
                'nombre' => 'RIEGO Y PRODUCCIÓN AGRÍCOLA',
                'codigo' => 'RPA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en sistemas de riego y producción agrícola sostenible',
                'activa' => true,
                'campus' => ['MATRIZ']
            ],
            [
                'nombre' => 'PRODUCCIÓN PECUARIA',
                'codigo' => 'PP',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en producción y manejo de animales de granja',
                'activa' => true,
                'campus' => ['MATRIZ']
            ],
            [
                'nombre' => 'MECÁNICA AUTOMOTRIZ',
                'codigo' => 'MA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en diagnóstico, mantenimiento y reparación de vehículos',
                'activa' => true,
                'campus' => ['GUARANDA']
            ],
            [
                'nombre' => 'ELECTRICIDAD',
                'codigo' => 'ELE',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en instalaciones y sistemas eléctricos',
                'activa' => true,
                'campus' => ['GUARANDA']
            ],
            [
                'nombre' => 'ELECTRÓNICA',
                'codigo' => 'ELEC',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en sistemas electrónicos y control',
                'activa' => true,
                'campus' => ['GUARANDA']
            ],
            [
                'nombre' => 'EDUCACIÓN INICIAL',
                'codigo' => 'EI',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en educación y desarrollo infantil',
                'activa' => true,
                'campus' => ['GUARANDA']
            ],
            [
                'nombre' => 'TECNOLOGÍA SUPERIOR EN DESARROLLO INFANTIL INTEGRAL',
                'codigo' => 'TSDII',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 4,
                'descripcion' => 'Tecnología superior en atención integral al desarrollo infantil',
                'activa' => true,
                'campus' => ['GUARANDA']
            ],
            [
                'nombre' => 'TECNOLOGÍA SUPERIOR EN ADMINISTRACIÓN',
                'codigo' => 'TSA',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en administración de empresas y organizaciones',
                'activa' => true,
                'campus' => ['SHIMIATUK']
            ],
            [
                'nombre' => 'ADMINISTRACIÓN',
                'codigo' => 'ADM',
                'tipo' => 'tecnica',
                'duracion_semestres' => 4,
                'descripcion' => 'Carrera técnica en administración empresarial',
                'activa' => true,
                'campus' => ['SHIMIATUK']
            ],
            [
                'nombre' => 'CONTABILIDAD',
                'codigo' => 'CONT',
                'tipo' => 'tecnologica',
                'duracion_semestres' => 5,
                'descripcion' => 'Tecnología superior en contabilidad y auditoría',
                'activa' => true,
                'campus' => ['PUYAHUATA']
            ],
            [
                'nombre' => 'CENTRO DE IDIOMAS',
                'codigo' => 'CI',
                'tipo' => 'tecnica',
                'duracion_semestres' => 2,
                'descripcion' => 'Programa de capacitación en idiomas extranjeros',
                'activa' => true,
                'campus' => ['23ABRIL', 'GUARANDA']
            ]
        ];

        foreach ($carreras as $carreraData) {
            $campusAsociados = $carreraData['campus'];
            unset($carreraData['campus']);

            $carrera = Carrera::create($carreraData);

            // Asociar la carrera con los campus correspondientes
            foreach ($campusAsociados as $codigoCampus) {
                $campus = Campus::where('codigo', $codigoCampus)->first();
                if ($campus) {
                    $carrera->campus()->attach($campus->id, [
                        'activa' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $this->command->info('Carreras creadas exitosamente con sus respectivos campus asociados.');
    }
}
