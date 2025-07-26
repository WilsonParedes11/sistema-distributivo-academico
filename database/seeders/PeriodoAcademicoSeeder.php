<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodoAcademico;
use Carbon\Carbon;

class PeriodoAcademicoSeeder extends Seeder
{
    public function run()
    {
        $periodos = [
            // Períodos académicos anteriores
            [
                'nombre' => '2023-I',
                'anio' => 2023,
                'periodo' => 'I',
                'fecha_inicio' => Carbon::create(2023, 3, 1),
                'fecha_fin' => Carbon::create(2023, 8, 31),
                'activo' => false,
            ],
            [
                'nombre' => '2023-II',
                'anio' => 2023,
                'periodo' => 'II',
                'fecha_inicio' => Carbon::create(2023, 9, 1),
                'fecha_fin' => Carbon::create(2024, 2, 28),
                'activo' => false,
            ],
            [
                'nombre' => '2024-I',
                'anio' => 2024,
                'periodo' => 'I',
                'fecha_inicio' => Carbon::create(2024, 3, 1),
                'fecha_fin' => Carbon::create(2024, 8, 31),
                'activo' => false,
            ],
            [
                'nombre' => '2024-II',
                'anio' => 2024,
                'periodo' => 'II',
                'fecha_inicio' => Carbon::create(2024, 9, 1),
                'fecha_fin' => Carbon::create(2025, 2, 28),
                'activo' => false,
            ],
            // Período académico actual (2025-I)
            [
                'nombre' => '2025-I',
                'anio' => 2025,
                'periodo' => 'I',
                'fecha_inicio' => Carbon::create(2025, 3, 1),
                'fecha_fin' => Carbon::create(2025, 8, 31),
                'activo' => true, // Período activo actual
            ],
            // Períodos académicos futuros
            [
                'nombre' => '2025-II',
                'anio' => 2025,
                'periodo' => 'II',
                'fecha_inicio' => Carbon::create(2025, 9, 1),
                'fecha_fin' => Carbon::create(2026, 2, 28),
                'activo' => false,
            ],
            [
                'nombre' => '2026-I',
                'anio' => 2026,
                'periodo' => 'I',
                'fecha_inicio' => Carbon::create(2026, 3, 1),
                'fecha_fin' => Carbon::create(2026, 8, 31),
                'activo' => false,
            ],
            [
                'nombre' => '2026-II',
                'anio' => 2026,
                'periodo' => 'II',
                'fecha_inicio' => Carbon::create(2026, 9, 1),
                'fecha_fin' => Carbon::create(2027, 2, 28),
                'activo' => false,
            ]
        ];

        foreach ($periodos as $periodo) {
            PeriodoAcademico::create($periodo);

            $estado = $periodo['activo'] ? '(ACTIVO)' : '';
            $this->command->info("Período académico creado: {$periodo['nombre']} {$estado}");
        }

        $this->command->info('Períodos académicos creados exitosamente.');
        $this->command->warn('Nota: El período 2025-I está configurado como ACTIVO.');
    }
}
