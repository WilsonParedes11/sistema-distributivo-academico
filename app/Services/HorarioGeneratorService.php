<?php
// app/Services/HorarioGeneratorService.php

namespace App\Services;

use App\Models\DistributivoAcademico;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\PeriodoAcademico;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HorarioGeneratorService
{
    private array $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    private array $horariosMatutina = [
        ['inicio' => '07:00', 'fin' => '08:00'],
        ['inicio' => '08:00', 'fin' => '09:00'],
        ['inicio' => '09:00', 'fin' => '10:00'],
        ['inicio' => '10:00', 'fin' => '11:00'],
        ['inicio' => '11:00', 'fin' => '12:00'],
        ['inicio' => '12:00', 'fin' => '13:00'],
    ];

    private array $horariosVespertina = [
        ['inicio' => '14:00', 'fin' => '15:00'],
        ['inicio' => '15:00', 'fin' => '16:00'],
        ['inicio' => '16:00', 'fin' => '17:00'],
        ['inicio' => '17:00', 'fin' => '18:00'],
        ['inicio' => '18:00', 'fin' => '19:00'],
        ['inicio' => '19:00', 'fin' => '20:00'],
    ];

    private array $horariosNocturna = [
        ['inicio' => '19:00', 'fin' => '20:00'],
        ['inicio' => '20:00', 'fin' => '21:00'],
        ['inicio' => '21:00', 'fin' => '22:00'],
        ['inicio' => '22:00', 'fin' => '23:00'],
    ];

    private array $horariosIntensiva = [
        ['inicio' => '08:00', 'fin' => '09:00'],
        ['inicio' => '09:00', 'fin' => '10:00'],
        ['inicio' => '10:00', 'fin' => '11:00'],
        ['inicio' => '11:00', 'fin' => '12:00'],
        ['inicio' => '14:00', 'fin' => '15:00'],
        ['inicio' => '15:00', 'fin' => '16:00'],
        ['inicio' => '16:00', 'fin' => '17:00'],
        ['inicio' => '17:00', 'fin' => '18:00'],
    ];

    public function generarHorarios(int $periodoAcademicoId, array $campusIds = []): array
    {
        $resultado = [
            'exitosos' => 0,
            'errores' => 0,
            'conflictos' => [],
            'mensajes' => []
        ];

        // Obtener distributivos del período
        $query = DistributivoAcademico::where('periodo_academico_id', $periodoAcademicoId)
            ->where('activo', true)
            ->with(['docente.user', 'asignatura', 'carrera', 'campus']);

        if (!empty($campusIds)) {
            $query->whereIn('campus_id', $campusIds);
        }

        $distributivos = $query->get();

        // Limpiar horarios existentes del período
        $this->limpiarHorariosExistentes($periodoAcademicoId, $campusIds);

        // Agrupar por docente para validar reglas
        $distributivosPorDocente = $distributivos->groupBy('docente_id');

        foreach ($distributivosPorDocente as $docenteId => $distributivosDocente) {
            $resultadoDocente = $this->generarHorariosDocente($distributivosDocente);

            $resultado['exitosos'] += $resultadoDocente['exitosos'];
            $resultado['errores'] += $resultadoDocente['errores'];
            $resultado['conflictos'] = array_merge($resultado['conflictos'], $resultadoDocente['conflictos']);
            $resultado['mensajes'] = array_merge($resultado['mensajes'], $resultadoDocente['mensajes']);
        }

        return $resultado;
    }

    private function generarHorariosDocente(Collection $distributivos): array
    {
        $resultado = [
            'exitosos' => 0,
            'errores' => 0,
            'conflictos' => [],
            'mensajes' => []
        ];

        $docente = $distributivos->first()->docente;
        $horariosAsignados = []; // Para controlar conflictos del docente

        foreach ($distributivos as $distributivo) {
            try {
                $horarios = $this->calcularHorariosParaDistributivo($distributivo, $horariosAsignados);

                foreach ($horarios as $horario) {
                    // Validar que el docente no tenga más de 3 horas por materia en el mismo día
                    if ($this->validarReglasDocente($horario, $horariosAsignados, $distributivo)) {
                        Horario::create($horario);
                        $horariosAsignados[] = $horario;
                        $resultado['exitosos']++;
                    } else {
                        $resultado['errores']++;
                        $resultado['conflictos'][] = [
                            'docente' => $docente->user->nombre_completo,
                            'asignatura' => $distributivo->asignatura->nombre,
                            'razon' => 'Excede límite de 3 horas por materia en el día o no son consecutivas'
                        ];
                    }
                }

                $resultado['mensajes'][] = "Horarios generados para {$docente->user->nombre_completo} - {$distributivo->asignatura->nombre}";

            } catch (\Exception $e) {
                $resultado['errores']++;
                $resultado['conflictos'][] = [
                    'docente' => $docente->user->nombre_completo,
                    'asignatura' => $distributivo->asignatura->nombre,
                    'razon' => $e->getMessage()
                ];
            }
        }

        return $resultado;
    }

    private function calcularHorariosParaDistributivo(DistributivoAcademico $distributivo, array $horariosExistentes): array
    {
        $horasClase = $distributivo->horas_clase_semana;
        $jornada = $distributivo->jornada;
        $campus = $distributivo->campus;

        // Seleccionar horarios según jornada
        $horariosDisponibles = $this->obtenerHorariosPorJornada($jornada);

        // Distribuir horas en la semana
        $distribuciones = $this->calcularDistribucionSemanal($horasClase);

        $horariosGenerados = [];
        $diasUsados = [];

        foreach ($distribuciones as $distribucion) {
            $horasBloque = $distribucion['horas'];
            $dia = $this->seleccionarDiaDisponible($diasUsados, $horariosExistentes, $distributivo);

            if (!$dia) {
                throw new \Exception("No se encontró día disponible para {$horasBloque} horas");
            }

            $horario = $this->seleccionarHorarioEnDia($dia, $horasBloque, $horariosDisponibles, $horariosExistentes, $distributivo);

            if (!$horario) {
                throw new \Exception("No se encontró horario disponible el {$dia} para {$horasBloque} horas");
            }

            $aula = $this->seleccionarAula($campus, $horario, $horariosExistentes);

            $horariosGenerados[] = [
                'distributivo_academico_id' => $distributivo->id,
                'dia_semana' => $dia,
                'hora_inicio' => $horario['inicio'],
                'hora_fin' => $horario['fin'],
                'aula' => $aula?->codigo,
                'edificio' => $aula?->edificio,
                'tipo_clase' => $distributivo->horas_componente_practico > 0 ? 'practica' : 'teorica',
            ];

            $diasUsados[] = $dia;
        }

        return $horariosGenerados;
    }

    private function validarReglasDocente(array $nuevoHorario, array $horariosExistentes, DistributivoAcademico $distributivo): bool
    {
        $dia = $nuevoHorario['dia_semana'];
        $distributivoId = $distributivo->id;

        // Contar horas ya asignadas en el mismo día para esta materia (distributivo)
        $horasEnDiaPorMateria = 0;
        $horariosDelDia = [];

        foreach ($horariosExistentes as $horario) {
            if ($horario['dia_semana'] === $dia && $horario['distributivo_academico_id'] === $distributivoId) {
                $inicio = Carbon::parse($horario['hora_inicio']);
                $fin = Carbon::parse($horario['hora_fin']);
                $horasEnDiaPorMateria += $fin->diffInHours($inicio);
                $horariosDelDia[] = $horario;
            }
        }

        // Calcular horas del nuevo horario
        $inicioNuevo = Carbon::parse($nuevoHorario['hora_inicio']);
        $finNuevo = Carbon::parse($nuevoHorario['hora_fin']);
        $horasNuevo = $finNuevo->diffInHours($inicioNuevo);

        // Validar: no más de 3 horas por materia en el mismo día
        if (($horasEnDiaPorMateria + $horasNuevo) > 3) {
            return false;
        }

        // Validar: las horas deben ser consecutivas
        if (!empty($horariosDelDia)) {
            foreach ($horariosDelDia as $horarioExistente) {
                $inicioExistente = Carbon::parse($horarioExistente['hora_inicio']);
                $finExistente = Carbon::parse($horarioExistente['hora_fin']);

                // Verificar si son consecutivos
                $sonConsecutivos = ($finExistente->format('H:i') === $inicioNuevo->format('H:i')) ||
                    ($finNuevo->format('H:i') === $inicioExistente->format('H:i'));

                if (!$sonConsecutivos) {
                    return false;
                }
            }
        }

        return true;
    }

    private function obtenerHorariosPorJornada(string $jornada): array
    {
        return match ($jornada) {
            'matutina' => $this->horariosMatutina,
            'vespertina' => $this->horariosVespertina,
            'nocturna' => $this->horariosNocturna,
            'intensiva' => $this->horariosIntensiva,
            default => $this->horariosMatutina
        };
    }

    private function calcularDistribucionSemanal(int $horasTotal): array
    {
        $distribuciones = [];

        if ($horasTotal <= 3) {
            // Una sola sesión
            $distribuciones[] = ['horas' => $horasTotal];
        } elseif ($horasTotal <= 6) {
            // Dos sesiones de 3 horas máximo
            $distribuciones[] = ['horas' => min(3, $horasTotal)];
            if ($horasTotal > 3) {
                $distribuciones[] = ['horas' => $horasTotal - 3];
            }
        } else {
            // Múltiples sesiones de máximo 3 horas
            $horasRestantes = $horasTotal;
            while ($horasRestantes > 0) {
                $horas = min(3, $horasRestantes);
                $distribuciones[] = ['horas' => $horas];
                $horasRestantes -= $horas;
            }
        }

        return $distribuciones;
    }

    private function seleccionarDiaDisponible(array $diasUsados, array $horariosExistentes, DistributivoAcademico $distributivo): ?string
    {
        $diasDisponibles = array_diff($this->diasSemana, $diasUsados);

        // Para jornada intensiva, priorizar sábado
        if ($distributivo->jornada === 'intensiva') {
            if (in_array('sabado', $diasDisponibles)) {
                return 'sabado';
            }
        }

        // Retornar el primer día disponible
        return !empty($diasDisponibles) ? array_values($diasDisponibles)[0] : null;
    }

    private function seleccionarHorarioEnDia(string $dia, int $horas, array $horariosDisponibles, array $horariosExistentes, DistributivoAcademico $distributivo): ?array
    {
        foreach ($horariosDisponibles as $horario) {
            $inicio = Carbon::parse($horario['inicio']);
            $fin = Carbon::parse($horario['fin']);
            $duracion = $fin->diffInHours($inicio);

            if ($duracion >= $horas) {
                // Verificar si está disponible
                if ($this->estaHorarioDisponible($dia, $horario['inicio'], $horario['fin'], $horariosExistentes, $distributivo)) {
                    // Ajustar fin si las horas son menores
                    if ($duracion > $horas) {
                        $finAjustado = $inicio->copy()->addHours($horas);
                        return [
                            'inicio' => $horario['inicio'],
                            'fin' => $finAjustado->format('H:i')
                        ];
                    }
                    return $horario;
                }
            }
        }

        return null;
    }

    private function estaHorarioDisponible(string $dia, string $inicio, string $fin, array $horariosExistentes, DistributivoAcademico $distributivo): bool
    {
        // Verificar conflictos con horarios existentes del mismo docente
        foreach ($horariosExistentes as $horario) {
            if ($horario['dia_semana'] === $dia) {
                if ($this->hayConflictoHorario($inicio, $fin, $horario['hora_inicio'], $horario['hora_fin'])) {
                    return false;
                }
            }
        }

        // Verificar conflictos con otros docentes en la misma aula
        $aulaDisponible = Aula::where('campus_id', $distributivo->campus_id)
            ->where('activa', true)
            ->whereDoesntHave('horarios', function ($query) use ($dia, $inicio, $fin) {
                $query->where('dia_semana', $dia)
                    ->where(function ($q) use ($inicio, $fin) {
                        $q->whereBetween('hora_inicio', [$inicio, $fin])
                            ->orWhereBetween('hora_fin', [$inicio, $fin])
                            ->orWhere(function ($q2) use ($inicio, $fin) {
                                $q2->where('hora_inicio', '<=', $inicio)
                                    ->where('hora_fin', '>=', $fin);
                            });
                    });
            })->exists();

        return $aulaDisponible;
    }

    private function hayConflictoHorario(string $inicio1, string $fin1, string $inicio2, string $fin2): bool
    {
        $inicio1 = Carbon::parse($inicio1);
        $fin1 = Carbon::parse($fin1);
        $inicio2 = Carbon::parse($inicio2);
        $fin2 = Carbon::parse($fin2);

        return ($inicio1 < $fin2) && ($fin1 > $inicio2);
    }

    private function seleccionarAula($campus, array $horario, array $horariosExistentes): ?Aula
    {
        return Aula::where('campus_id', $campus->id)
            ->where('activa', true)
            ->whereDoesntHave('horarios', function ($query) use ($horario) {
                $query->where('dia_semana', $horario['dia_semana'] ?? '')
                    ->where('hora_inicio', $horario['inicio'])
                    ->where('hora_fin', $horario['fin']);
            })
            ->first();
    }

    private function limpiarHorariosExistentes(int $periodoAcademicoId, array $campusIds = []): void
    {
        $query = Horario::whereHas('distributivoAcademico', function ($q) use ($periodoAcademicoId, $campusIds) {
            $q->where('periodo_academico_id', $periodoAcademicoId);
            if (!empty($campusIds)) {
                $q->whereIn('campus_id', $campusIds);
            }
        });

        $query->delete();
    }

    public function obtenerHorarioDocente(int $docenteId, int $periodoAcademicoId): Collection
    {
        return Horario::whereHas('distributivoAcademico', function ($query) use ($docenteId, $periodoAcademicoId) {
            $query->where('docente_id', $docenteId)
                ->where('periodo_academico_id', $periodoAcademicoId);
        })->with(['distributivoAcademico.asignatura', 'distributivoAcademico.carrera'])
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
    }

    public function obtenerHorarioCarrera(int $carreraId, int $semestre, string $paralelo, int $campusId, int $periodoAcademicoId): Collection
    {
        return Horario::whereHas('distributivoAcademico', function ($query) use ($carreraId, $semestre, $paralelo, $campusId, $periodoAcademicoId) {
            $query->where('carrera_id', $carreraId)
                ->where('semestre', $semestre)
                ->where('paralelo', $paralelo)
                ->where('campus_id', $campusId)
                ->where('periodo_academico_id', $periodoAcademicoId);
        })->with(['distributivoAcademico.asignatura', 'distributivoAcademico.docente.user'])
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();
    }
}
