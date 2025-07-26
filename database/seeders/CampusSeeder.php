<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campus;

class CampusSeeder extends Seeder
{
    public function run()
    {
        $campus = [
            [
                'nombre' => 'MATRIZ',
                'codigo' => 'MATRIZ',
                'direccion' => 'Dirección Principal',
                'telefono' => '032981000',
                'activo' => true
            ],
            [
                'nombre' => '23 DE ABRIL',
                'codigo' => '23ABRIL',
                'direccion' => 'Dirección Campus 23 de Abril',
                'telefono' => '032981001',
                'activo' => true
            ],
            [
                'nombre' => 'GUARANDA',
                'codigo' => 'GUARANDA',
                'direccion' => 'Dirección Campus Guaranda',
                'telefono' => '032981002',
                'activo' => true
            ],
            [
                'nombre' => 'SHIMIATUK',
                'codigo' => 'SHIMIATUK',
                'direccion' => 'Dirección Campus Shimiatuk',
                'telefono' => '032981003',
                'activo' => true
            ],
            [
                'nombre' => 'PUYAHUATA',
                'codigo' => 'PUYAHUATA',
                'direccion' => 'Dirección Campus Puyahuata',
                'telefono' => '032981004',
                'activo' => true
            ]
        ];

        foreach ($campus as $campusData) {
            Campus::create($campusData);
        }
    }
}
