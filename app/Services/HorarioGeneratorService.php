<?php
// app/Services/HorarioGeneratorService.php

namespace App\Services;

use App\Models\DistributivoAcademico;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\PeriodoAcademico;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HorarioGeneratorService
{
    private array $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    private array $diasSemanaIntensiva = ['sabado'];

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

        try {
            // Validar que el período académico existe
            $periodo = PeriodoAcademico::find($periodoAcademicoId);
            if (!$periodo) {
                throw new \Exception("El período académico con ID {$periodoAcademicoId} no existe");
            }

            // Obtener distributivos del período
            $query = DistributivoAcademico::where('periodo_academico_id', $periodoAcademicoId)
                ->where('activo', true)
                ->with(['docente.user', 'asignatura', 'carrera', 'campus']);

            if (!empty($campusIds)) {
                $query->whereIn('campus_id', $campusIds);
            }

            $distributivos = $query->get();

            if ($distributivos->isEmpty()) {
                $resultado['mensajes'][] = "No se encontraron distributivos activos para el período académico";
                return $resultado;
            }

            // Validar que hay aulas disponibles
            $this->validarAulasDisponibles($distributivos, $campusIds);

            // Limpiar horarios existentes del período
            $this->limpiarHorariosExistentes($periodoAcademicoId, $campusIds);

            // Procesamiento ordenado por carga académica
            $resultado = $this->procesarDistributivosOrdenados($distributivos);

        } catch (\Exception $e) {
            $resultado['errores']++;
            $resultado['conflictos'][] = [
                'error_general' => $e->getMessage()
            ];
            Log::error("Error en generarHorarios: " . $e->getMessage());
        }

        return $resultado;
    }

    private function procesarDistributivosOrdenados(Collection $distributivos): array
    {
        $resultado = [
            'exitosos' => 0,
            'errores' => 0,
            'conflictos' => [],
            'mensajes' => []
        ];

        // Agrupar por docente y calcular carga total
        $distributivosPorDocente = $distributivos->groupBy('docente_id')->map(function ($distribs) {
            $cargaTotal = $distribs->sum('horas_clase_semana');
            return [
                'distributivos' => $distribs,
                'carga_total' => $cargaTotal,
                'docente' => $distribs->first()->docente
            ];
        });

        // Ordenar docentes por carga académica (menor a mayor)
        $docentesOrdenados = $distributivosPorDocente->sortBy('carga_total');

        $horariosGlobalesAsignados = []; // Todos los horarios asignados globalmente

        foreach ($docentesOrdenados as $docenteId => $docenteData) {
            Log::info("Procesando docente: {$docenteData['docente']->user->nombre_completo} - Carga total: {$docenteData['carga_total']} horas");

            // Ordenar materias del docente por horas (mayor a menor)
            $distributivosDocente = $docenteData['distributivos']->sortByDesc('horas_clase_semana');

            foreach ($distributivosDocente as $distributivo) {
                try {
                    $horarios = $this->calcularHorariosParaDistributivoMejorado(
                        $distributivo,
                        $horariosGlobalesAsignados
                    );

                    if (empty($horarios)) {
                        $resultado['errores']++;
                        $resultado['conflictos'][] = [
                            'docente' => $docenteData['docente']->user->nombre_completo,
                            'asignatura' => $distributivo->asignatura->nombre,
                            'razon' => 'No se pudieron generar horarios para este distributivo con la carga actual'
                        ];
                        continue;
                    }

                    $horariosValidosCreados = 0;
                    foreach ($horarios as $horario) {
                        // Validación final antes de crear
                        if ($this->validarReglasDocente($horario, $horariosGlobalesAsignados, $distributivo)) {
                            try {
                                Horario::create($horario);
                                $horariosGlobalesAsignados[] = $horario;
                                $horariosValidosCreados++;
                                $resultado['exitosos']++;
                            } catch (\Exception $e) {
                                Log::error("Error creando horario: " . $e->getMessage());
                            }
                        }
                    }

                    if ($horariosValidosCreados > 0) {
                        $resultado['mensajes'][] = "Horarios generados para {$docenteData['docente']->user->nombre_completo} - {$distributivo->asignatura->nombre}: {$horariosValidosCreados} horarios";
                    }

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['conflictos'][] = [
                        'docente' => $docenteData['docente']->user->nombre_completo,
                        'asignatura' => $distributivo->asignatura->nombre,
                        'razon' => $e->getMessage()
                    ];
                    Log::error("Error procesando distributivo {$distributivo->id}: " . $e->getMessage());
                }
            }
        }

        return $resultado;
    }

    private function calcularHorariosParaDistributivoMejorado(DistributivoAcademico $distributivo, array $horariosExistentes): array
    {
        $horasClase = $distributivo->horas_clase_semana; // 7 horas
        $horasPracticas = $distributivo->horas_componente_practico; // 3 horas
        $horasTeoricas = $horasClase - $horasPracticas; // 4 horas
        $jornada = $distributivo->jornada;
        $campus = $distributivo->campus;
        $semestre = $distributivo->semestre;

        // Validar datos básicos
        if ($horasClase <= 0 || $horasPracticas < 0 || $horasPracticas > $horasClase) {
            throw new \Exception("Horas inválidas: horas_clase_semana={$horasClase}, horas_componente_practico={$horasPracticas}");
        }

        // Seleccionar horarios según jornada
        $horariosDisponibles = $this->obtenerHorariosPorJornada($jornada);

        if (empty($horariosDisponibles)) {
            throw new \Exception("No hay horarios definidos para la jornada: {$jornada}");
        }

        // Seleccionar una única aula para la asignatura si es de primer semestre
        $aulaAsignada = null;
        if ($semestre == 1) {
            $aulaAsignada = $this->seleccionarAulaParaAsignatura($campus, $distributivo->id, $jornada);
            if (!$aulaAsignada) {
                throw new \Exception("No se encontró aula disponible para la asignatura {$distributivo->asignatura->nombre} en el campus {$campus->id}");
            }
        }

        // Distribuir horas prácticas y teóricas por separado
        $distribucionesPracticas = $horasPracticas > 0 ? $this->calcularDistribucionOptima($horasPracticas) : [];
        $distribucionesTeoricas = $horasTeoricas > 0 ? $this->calcularDistribucionOptima($horasTeoricas) : [];
        $horariosGenerados = [];

        // Determinar días válidos según jornada
        $diasValidos = $jornada === 'intensiva' ? $this->diasSemanaIntensiva : $this->diasSemana;

        // Buscar los mejores días disponibles para este docente
        $diasDisponiblesParaDocente = $this->encontrarDiasDisponiblesParaDocente($distributivo, $diasValidos, $horariosExistentes);

        // Asignar horarios prácticos
        foreach ($distribucionesPracticas as $distribucion) {
            $horasBloque = $distribucion['horas'];
            $diaAsignado = false;

            foreach ($diasDisponiblesParaDocente as $diaInfo) {
                $dia = $diaInfo['dia'];

                try {
                    $horariosParaDia = $this->seleccionarHorarioEnDiaMejorado(
                        $dia,
                        $horasBloque,
                        $horariosDisponibles,
                        array_merge($horariosExistentes, $horariosGenerados),
                        $distributivo
                    );

                    if (!empty($horariosParaDia)) {
                        $horariosDiaCompletos = [];
                        $todosLosHorariosValidos = true;

                        foreach ($horariosParaDia as $horario) {
                            $horarioTemp = [
                                'distributivo_academico_id' => $distributivo->id,
                                'dia_semana' => $dia,
                                'hora_inicio' => $horario['inicio'],
                                'hora_fin' => $horario['fin'],
                                'tipo_clase' => 'practica',
                            ];

                            if ($this->validarReglasDocenteBasico($horarioTemp, array_merge($horariosExistentes, $horariosGenerados), $distributivo)) {
                                // Usar la misma aula para primer semestre, o seleccionar una nueva si no es primer semestre
                                $aula = ($semestre == 1) ? $aulaAsignada : $this->seleccionarAula($campus, $dia, $horario['inicio'], $horario['fin'], $distributivo->id);

                                if ($aula) {
                                    $horarioTemp['aula'] = $aula->codigo;
                                    $horarioTemp['edificio'] = $aula->edificio;
                                    $horariosDiaCompletos[] = $horarioTemp;
                                } else {
                                    $todosLosHorariosValidos = false;
                                    break;
                                }
                            } else {
                                $todosLosHorariosValidos = false;
                                break;
                            }
                        }

                        if ($todosLosHorariosValidos && count($horariosDiaCompletos) === count($horariosParaDia)) {
                            $horariosGenerados = array_merge($horariosGenerados, $horariosDiaCompletos);
                            $diaAsignado = true;
                            Log::info("Día {$dia} asignado para distributivo {$distributivo->id} - {$distributivo->asignatura->nombre} (práctico)");
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Error procesando día {$dia} para distributivo {$distributivo->id}: " . $e->getMessage());
                }
            }

            if (!$diaAsignado) {
                throw new \Exception("No se encontró día y horario disponibles para {$horasBloque} horas prácticas en la jornada {$jornada}. Aulas activas: " . Aula::where('campus_id', $distributivo->campus_id)->where('activa', true)->count());
            }
        }

        // Asignar horarios teóricos
        foreach ($distribucionesTeoricas as $distribucion) {
            $horasBloque = $distribucion['horas'];
            $diaAsignado = false;

            foreach ($diasDisponiblesParaDocente as $diaInfo) {
                $dia = $diaInfo['dia'];

                try {
                    $horariosParaDia = $this->seleccionarHorarioEnDiaMejorado(
                        $dia,
                        $horasBloque,
                        $horariosDisponibles,
                        array_merge($horariosExistentes, $horariosGenerados),
                        $distributivo
                    );

                    if (!empty($horariosParaDia)) {
                        $horariosDiaCompletos = [];
                        $todosLosHorariosValidos = true;

                        foreach ($horariosParaDia as $horario) {
                            $horarioTemp = [
                                'distributivo_academico_id' => $distributivo->id,
                                'dia_semana' => $dia,
                                'hora_inicio' => $horario['inicio'],
                                'hora_fin' => $horario['fin'],
                                'tipo_clase' => 'teorica',
                            ];

                            if ($this->validarReglasDocenteBasico($horarioTemp, array_merge($horariosExistentes, $horariosGenerados), $distributivo)) {
                                // Usar la misma aula para primer semestre, o seleccionar una nueva si no es primer semestre
                                $aula = ($semestre == 1) ? $aulaAsignada : $this->seleccionarAula($campus, $dia, $horario['inicio'], $horario['fin'], $distributivo->id);

                                if ($aula) {
                                    $horarioTemp['aula'] = $aula->codigo;
                                    $horarioTemp['edificio'] = $aula->edificio;
                                    $horariosDiaCompletos[] = $horarioTemp;
                                } else {
                                    $todosLosHorariosValidos = false;
                                    break;
                                }
                            } else {
                                $todosLosHorariosValidos = false;
                                break;
                            }
                        }

                        if ($todosLosHorariosValidos && count($horariosDiaCompletos) === count($horariosParaDia)) {
                            $horariosGenerados = array_merge($horariosGenerados, $horariosDiaCompletos);
                            $diaAsignado = true;
                            Log::info("Día {$dia} asignado para distributivo {$distributivo->id} - {$distributivo->asignatura->nombre} (teórico)");
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Error procesando día {$dia} para distributivo {$distributivo->id}: " . $e->getMessage());
                }
            }

            if (!$diaAsignado) {
                throw new \Exception("No se encontró día y horario disponibles para {$horasBloque} horas teóricas en la jornada {$jornada}. Aulas activas: " . Aula::where('campus_id', $distributivo->campus_id)->where('activa', true)->count());
            }
        }

        // Verificar que se asignaron todas las horas requeridas
        $horasAsignadas = 0;
        $horasPracticasAsignadas = 0;
        foreach ($horariosGenerados as $horario) {
            $inicio = Carbon::parse($horario['hora_inicio']);
            $fin = Carbon::parse($horario['hora_fin']);
            $horasBloque = $fin->diffInHours($inicio);
            $horasAsignadas += $horasBloque;
            if ($horario['tipo_clase'] === 'practica') {
                $horasPracticasAsignadas += $horasBloque;
            }
        }

        if ($horasAsignadas !== $horasClase) {
            throw new \Exception("No se asignaron todas las horas requeridas para {$distributivo->asignatura->nombre}. Horas asignadas: {$horasAsignadas}, Horas requeridas: {$horasClase}");
        }

        if ($horasPracticasAsignadas !== $horasPracticas) {
            throw new \Exception("No se asignaron las horas prácticas requeridas para {$distributivo->asignatura->nombre}. Horas prácticas asignadas: {$horasPracticasAsignadas}, Horas prácticas requeridas: {$horasPracticas}");
        }

        return $horariosGenerados;
    }

    private function seleccionarAulaParaAsignatura($campus, int $distributivoId, string $jornada): ?Aula
    {
        // Seleccionar una aula disponible para toda la asignatura, considerando todos los horarios posibles de la jornada
        $horariosDisponibles = $this->obtenerHorariosPorJornada($jornada);
        $aula = Aula::where('campus_id', $campus->id)
            ->where('activa', true)
            ->whereNotExists(function ($query) use ($distributivoId, $horariosDisponibles) {
                $query->select(DB::raw(1))
                    ->from('horarios')
                    ->whereRaw('horarios.aula = aulas.codigo')
                    ->whereIn('dia_semana', $this->diasSemana)
                    ->where(function ($q) use ($horariosDisponibles) {
                        foreach ($horariosDisponibles as $horario) {
                            $q->orWhere(function ($q2) use ($horario) {
                                $q2->where('hora_inicio', '<', $horario['fin'])
                                    ->where('hora_fin', '>', $horario['inicio']);
                            });
                        }
                    });
            })
            ->first();

        if (!$aula) {
            Log::warning("No se encontró aula disponible para distributivo {$distributivoId} en campus {$campus->id} para la jornada {$jornada}. Aulas activas totales: " . Aula::where('campus_id', $campus->id)->where('activa', true)->count());
        }

        return $aula;
    }

    private function encontrarDiasDisponiblesParaDocente(DistributivoAcademico $distributivo, array $diasValidos, array $horariosExistentes): array
    {
        $disponibilidadPorDia = [];
        $docenteId = $distributivo->docente_id;

        foreach ($diasValidos as $dia) {
            $horariosOcupadosDocente = 0;

            foreach ($horariosExistentes as $horario) {
                if ($horario['dia_semana'] === $dia) {
                    $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                    if ($otroDistributivo && $otroDistributivo->docente_id === $docenteId) {
                        $horariosOcupadosDocente++;
                    }
                }
            }

            $disponibilidadPorDia[] = [
                'dia' => $dia,
                'horarios_ocupados' => $horariosOcupadosDocente,
                'disponibilidad' => 4 - $horariosOcupadosDocente // máximo 4 horarios por día en nocturna
            ];
        }

        usort($disponibilidadPorDia, function ($a, $b) {
            return $b['disponibilidad'] <=> $a['disponibilidad'];
        });

        return $disponibilidadPorDia;
    }

    private function calcularDistribucionOptima(int $horasTotal): array
    {
        $distribuciones = [];
        $horasRestantes = $horasTotal;

        while ($horasRestantes > 0) {
            if ($horasRestantes >= 3) {
                $distribuciones[] = ['horas' => 3];
                $horasRestantes -= 3;
            } elseif ($horasRestantes >= 2) {
                $distribuciones[] = ['horas' => 2];
                $horasRestantes -= 2;
            } else {
                $distribuciones[] = ['horas' => 1];
                $horasRestantes -= 1;
            }
        }

        Log::info("Distribución óptima para {$horasTotal} horas: " . json_encode($distribuciones));
        return $distribuciones;
    }

    private function seleccionarHorarioEnDiaMejorado(string $dia, int $horas, array $horariosDisponibles, array $horariosExistentes, DistributivoAcademico $distributivo): array
    {
        $horasYaAsignadasEnDia = $this->contarHorasAsignadasEnDia($dia, $distributivo->id, $horariosExistentes);

        if ($horasYaAsignadasEnDia >= 3) {
            return [];
        }

        $horasMaximasPermitidas = 3 - $horasYaAsignadasEnDia;
        $horasAAsignar = min($horas, $horasMaximasPermitidas);

        $mejorBloque = $this->encontrarMejorBloqueConsecutivo($dia, $horasAAsignar, $horariosDisponibles, $horariosExistentes, $distributivo);

        return $mejorBloque;
    }

    private function encontrarMejorBloqueConsecutivo(string $dia, int $horas, array $horariosDisponibles, array $horariosExistentes, DistributivoAcademico $distributivo): array
    {
        for ($tamanoBloque = $horas; $tamanoBloque >= 1; $tamanoBloque--) {
            for ($i = 0; $i <= count($horariosDisponibles) - $tamanoBloque; $i++) {
                $bloque = [];
                $esValido = true;

                for ($j = 0; $j < $tamanoBloque; $j++) {
                    $horarioActual = $horariosDisponibles[$i + $j];

                    if (!$this->estaHorarioDisponible($dia, $horarioActual['inicio'], $horarioActual['fin'], $horariosExistentes, $distributivo)) {
                        $esValido = false;
                        break;
                    }

                    if ($j > 0) {
                        $horarioAnterior = $horariosDisponibles[$i + $j - 1];
                        if ($horarioAnterior['fin'] !== $horarioActual['inicio']) {
                            $esValido = false;
                            break;
                        }
                    }

                    $bloque[] = $horarioActual;
                }

                if ($esValido && count($bloque) === $tamanoBloque) {
                    return $bloque; // Relajamos la consecutividad con horarios existentes
                }
            }
        }

        return [];
    }

    private function validarReglasDocenteBasico(array $nuevoHorario, array $horariosExistentes, DistributivoAcademico $distributivo): bool
    {
        $dia = $nuevoHorario['dia_semana'];
        $docenteId = $distributivo->docente_id;

        foreach ($horariosExistentes as $horario) {
            if ($horario['dia_semana'] === $dia) {
                $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                if (
                    $otroDistributivo && $otroDistributivo->docente_id === $docenteId &&
                    $this->hayConflictoHorario($nuevoHorario['hora_inicio'], $nuevoHorario['hora_fin'], $horario['hora_inicio'], $horario['hora_fin'])
                ) {
                    Log::warning("Conflicto de horario para docente {$docenteId} en {$dia} de {$nuevoHorario['hora_inicio']} a {$nuevoHorario['hora_fin']} con distributivo {$horario['distributivo_academico_id']}");
                    return false;
                }
            }
        }

        return true;
    }

    private function validarAulasDisponibles(Collection $distributivos, array $campusIds): void
    {
        $campusIdsDistributivos = $distributivos->pluck('campus_id')->unique();

        foreach ($campusIdsDistributivos as $campusId) {
            $aulasActivas = Aula::where('campus_id', $campusId)
                ->where('activa', true)
                ->count();

            if ($aulasActivas === 0) {
                throw new \Exception("No hay aulas activas disponibles en el campus ID: {$campusId}");
            }
        }
    }

    private function validarDistributivo(DistributivoAcademico $distributivo): bool
    {
        if (!$distributivo->docente || !$distributivo->asignatura || !$distributivo->campus) {
            return false;
        }

        if (!$distributivo->horas_clase_semana || $distributivo->horas_clase_semana <= 0) {
            return false;
        }

        if ($distributivo->horas_componente_practico > $distributivo->horas_clase_semana) {
            return false;
        }

        if (!in_array($distributivo->jornada, ['matutina', 'vespertina', 'nocturna', 'intensiva'])) {
            return false;
        }

        return true;
    }

    private function calcularHorariosParaDistributivo(DistributivoAcademico $distributivo, array $horariosExistentes): array
    {
        $horasClase = $distributivo->horas_clase_semana;
        $jornada = $distributivo->jornada;
        $campus = $distributivo->campus;
        $semestre = $distributivo->semestre;

        if ($horasClase <= 0) {
            throw new \Exception("Horas de clase inválidas: {$horasClase}");
        }

        $horariosDisponibles = $this->obtenerHorariosPorJornada($jornada);

        if (empty($horariosDisponibles)) {
            throw new \Exception("No hay horarios definidos para la jornada: {$jornada}");
        }

        $aulaAsignada = null;
        if ($semestre == 1) {
            $aulaAsignada = $this->seleccionarAulaParaAsignatura($campus, $distributivo->id, $jornada);
            if (!$aulaAsignada) {
                throw new \Exception("No se encontró aula disponible para la asignatura {$distributivo->asignatura->nombre} en el campus {$campus->id}");
            }
        }

        $distribuciones = $this->calcularDistribucionSemanal($horasClase);
        $horariosGenerados = [];

        $diasValidos = $jornada === 'intensiva' ? $this->diasSemanaIntensiva : $this->diasSemana;

        foreach ($distribuciones as $distribucion) {
            $horasBloque = $distribucion['horas'];
            $diaAsignado = false;

            foreach ($diasValidos as $dia) {
                try {
                    $horariosParaDia = $this->seleccionarHorarioEnDia(
                        $dia,
                        $horasBloque,
                        $horariosDisponibles,
                        array_merge($horariosExistentes, $horariosGenerados),
                        $distributivo
                    );

                    if (!empty($horariosParaDia)) {
                        $horariosDiaCompletos = [];
                        $todosLosHorariosValidos = true;

                        foreach ($horariosParaDia as $horario) {
                            $horarioTemp = [
                                'distributivo_academico_id' => $distributivo->id,
                                'dia_semana' => $dia,
                                'hora_inicio' => $horario['inicio'],
                                'hora_fin' => $horario['fin'],
                                'tipo_clase' => $distributivo->horas_componente_practico > 0 ? 'practica' : 'teorica',
                            ];

                            if ($this->validarReglasDocente($horarioTemp, array_merge($horariosExistentes, $horariosGenerados), $distributivo)) {
                                $aula = ($semestre == 1) ? $aulaAsignada : $this->seleccionarAula($campus, $dia, $horario['inicio'], $horario['fin'], $distributivo->id);

                                if ($aula) {
                                    $horarioTemp['aula'] = $aula->codigo;
                                    $horarioTemp['edificio'] = $aula->edificio;
                                    $horariosDiaCompletos[] = $horarioTemp;
                                } else {
                                    Log::warning("No se encontró aula disponible para {$dia} de {$horario['inicio']} a {$horario['fin']}");
                                    $todosLosHorariosValidos = false;
                                    break;
                                }
                            } else {
                                Log::warning("Horario no válido por reglas del docente: {$dia} de {$horario['inicio']} a {$horario['fin']}");
                                $todosLosHorariosValidos = false;
                                break;
                            }
                        }

                        if ($todosLosHorariosValidos && count($horariosDiaCompletos) === count($horariosParaDia)) {
                            $horariosGenerados = array_merge($horariosGenerados, $horariosDiaCompletos);
                            $diaAsignado = true;
                            Log::info("Día {$dia} asignado exitosamente para distributivo {$distributivo->id} con " . count($horariosDiaCompletos) . " horarios");
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Error procesando día {$dia}: " . $e->getMessage());
                }
            }

            if (!$diaAsignado) {
                $aulasActivas = Aula::where('campus_id', $distributivo->campus_id)
                    ->where('activa', true)
                    ->count();

                throw new \Exception("No se encontró día y horario disponibles para {$horasBloque} horas en la jornada {$jornada}. Aulas activas en campus {$distributivo->campus_id}: {$aulasActivas}. Días intentados: " . implode(', ', $diasValidos));
            }
        }

        return $horariosGenerados;
    }

    private function obtenerHorariosPorJornada(string $jornada): array
    {
        return match ($jornada) {
            'matutina' => $this->horariosMatutina,
            'vespertina' => $this->horariosVespertina,
            'nocturna' => $this->horariosNocturna,
            'intensiva' => $this->horariosIntensiva,
            default => throw new \Exception("Jornada no válida: {$jornada}")
        };
    }

    private function calcularDistribucionSemanal(int $horasTotal): array
    {
        $distribuciones = [];
        $horasRestantes = $horasTotal;

        while ($horasRestantes > 0) {
            if ($horasRestantes >= 3) {
                $distribuciones[] = ['horas' => 3];
                $horasRestantes -= 3;
            } elseif ($horasRestantes >= 2) {
                $distribuciones[] = ['horas' => 2];
                $horasRestantes -= 2;
            } else {
                $distribuciones[] = ['horas' => 1];
                $horasRestantes -= 1;
            }
        }

        Log::info("Distribución semanal para {$horasTotal} horas: " . json_encode($distribuciones));
        return $distribuciones;
    }

    private function seleccionarHorarioEnDia(string $dia, int $horas, array $horariosDisponibles, array $horariosExistentes, DistributivoAcademico $distributivo): array
    {
        $horasYaAsignadasEnDia = $this->contarHorasAsignadasEnDia($dia, $distributivo->id, $horariosExistentes);

        if ($horasYaAsignadasEnDia >= 3) {
            Log::warning("Ya se han asignado {$horasYaAsignadasEnDia} horas para la materia {$distributivo->asignatura->nombre} el {$dia}");
            return [];
        }

        $horasMaximasPermitidas = 3 - $horasYaAsignadasEnDia;
        $horasAAsignar = min($horas, $horasMaximasPermitidas);

        for ($i = 0; $i <= count($horariosDisponibles) - $horasAAsignar; $i++) {
            $bloque = [];
            $esConsecutivo = true;

            for ($j = 0; $j < $horasAAsignar; $j++) {
                $horarioActual = $horariosDisponibles[$i + $j];

                if (!$this->estaHorarioDisponible($dia, $horarioActual['inicio'], $horarioActual['fin'], $horariosExistentes, $distributivo)) {
                    $esConsecutivo = false;
                    break;
                }

                if ($j > 0) {
                    $horarioAnterior = $horariosDisponibles[$i + $j - 1];
                    if ($horarioAnterior['fin'] !== $horarioActual['inicio']) {
                        $esConsecutivo = false;
                        break;
                    }
                }

                $bloque[] = $horarioActual;
            }

            if ($esConsecutivo && count($bloque) === $horasAAsignar) {
                return $bloque; // Relajamos la consecutividad con horarios existentes
            }
        }

        if ($horasAAsignar === 1 && $horasYaAsignadasEnDia === 0) {
            foreach ($horariosDisponibles as $horario) {
                if ($this->estaHorarioDisponible($dia, $horario['inicio'], $horario['fin'], $horariosExistentes, $distributivo)) {
                    return [$horario];
                }
            }
        }

        return [];
    }

    private function contarHorasAsignadasEnDia(string $dia, int $distributivoId, array $horariosExistentes): int
    {
        $horasTotal = 0;

        foreach ($horariosExistentes as $horario) {
            if (
                $horario['dia_semana'] === $dia &&
                isset($horario['distributivo_academico_id']) &&
                $horario['distributivo_academico_id'] === $distributivoId
            ) {
                $inicio = Carbon::parse($horario['hora_inicio']);
                $fin = Carbon::parse($horario['hora_fin']);
                $horasTotal += $fin->diffInHours($inicio);
            }
        }

        return $horasTotal;
    }

    private function esConsecutivoConHorariosExistentes(string $dia, array $nuevosHorarios, int $distributivoId, array $horariosExistentes): bool
    {
        // Relajamos la restricción de consecutividad para facilitar la asignación
        return true;
    }

    private function estaHorarioDisponible(string $dia, string $inicio, string $fin, array $horariosExistentes, DistributivoAcademico $distributivo): bool
    {
        $docenteId = $distributivo->docente_id;

        foreach ($horariosExistentes as $horario) {
            if ($horario['dia_semana'] === $dia) {
                $horarioDocenteId = null;
                if (isset($horario['distributivo_academico_id'])) {
                    $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                    if ($otroDistributivo) {
                        $horarioDocenteId = $otroDistributivo->docente_id;
                    }
                }

                if ($horarioDocenteId === $docenteId) {
                    if ($this->hayConflictoHorario($inicio, $fin, $horario['hora_inicio'], $horario['hora_fin'])) {
                        Log::warning("Conflicto de horario para docente {$docenteId} el {$dia} de {$inicio} a {$fin} - ya tiene clase de {$horario['hora_inicio']} a {$horario['hora_fin']} en distributivo {$horario['distributivo_academico_id']}");
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function hayConflictoHorario(string $inicio1, string $fin1, string $inicio2, string $fin2): bool
    {
        $inicio1 = Carbon::parse($inicio1);
        $fin1 = Carbon::parse($fin1);
        $inicio2 = Carbon::parse($inicio2);
        $fin2 = Carbon::parse($fin2);

        return ($inicio1 < $fin2) && ($fin1 > $inicio2);
    }

    private function seleccionarAula($campus, string $dia, string $inicio, string $fin, int $distributivoId): ?Aula
    {
        $aula = Aula::where('campus_id', $campus->id)
            ->where('activa', true)
            ->whereNotExists(function ($query) use ($dia, $inicio, $fin) {
                $query->select(DB::raw(1))
                    ->from('horarios')
                    ->whereRaw('horarios.aula = aulas.codigo')
                    ->where('dia_semana', $dia)
                    ->where(function ($q) use ($inicio, $fin) {
                        $q->where('hora_inicio', '<', $fin)
                            ->where('hora_fin', '>', $inicio);
                    });
            })
            ->first();

        if (!$aula) {
            Log::warning("No se encontró aula disponible para distributivo {$distributivoId} en {$dia} de {$inicio} a {$fin} en campus {$campus->id}. Aulas activas totales: " . Aula::where('campus_id', $campus->id)->where('activa', true)->count());
        }

        return $aula;
    }

    private function validarReglasDocente(array $nuevoHorario, array $horariosExistentes, DistributivoAcademico $distributivo): bool
    {
        $dia = $nuevoHorario['dia_semana'];
        $distributivoId = $distributivo->id;
        $docenteId = $distributivo->docente_id;

        foreach ($horariosExistentes as $horario) {
            if ($horario['dia_semana'] === $dia) {
                $horarioDocenteId = null;
                if (isset($horario['distributivo_academico_id'])) {
                    $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                    if ($otroDistributivo) {
                        $horarioDocenteId = $otroDistributivo->docente_id;
                    }
                }

                if (
                    $horarioDocenteId === $docenteId &&
                    isset($horario['distributivo_academico_id']) &&
                    $horario['distributivo_academico_id'] !== $distributivoId
                ) {
                    if ($this->hayConflictoHorario($nuevoHorario['hora_inicio'], $nuevoHorario['hora_fin'], $horario['hora_inicio'], $horario['hora_fin'])) {
                        Log::warning("VALIDACIÓN FALLIDA: Conflicto de horario con otra materia del mismo docente {$docenteId} en el día {$dia}");
                        return false;
                    }
                }
            }
        }

        $horasEnDiaPorMateria = $this->contarHorasAsignadasEnDia($dia, $distributivoId, $horariosExistentes);

        $inicioNuevo = Carbon::parse($nuevoHorario['hora_inicio']);
        $finNuevo = Carbon::parse($nuevoHorario['hora_fin']);
        $horasNuevo = $finNuevo->diffInHours($inicioNuevo);

        if (($horasEnDiaPorMateria + $horasNuevo) > 3) {
            Log::warning("VALIDACIÓN FALLIDA: Excede límite de 3 horas por materia en el día {$dia} para distributivo {$distributivoId}. Horas existentes: {$horasEnDiaPorMateria}, Horas nuevas: {$horasNuevo}");
            return false;
        }

        return true;
    }

    private function limpiarHorariosExistentes(int $periodoAcademicoId, array $campusIds = []): void
    {
        try {
            $query = Horario::whereHas('distributivoAcademico', function ($q) use ($periodoAcademicoId, $campusIds) {
                $q->where('periodo_academico_id', $periodoAcademicoId);
                if (!empty($campusIds)) {
                    $q->whereIn('campus_id', $campusIds);
                }
            });

            $eliminados = $query->count();
            $query->delete();

            Log::info("Eliminados {$eliminados} horarios existentes para el período {$periodoAcademicoId}");
        } catch (\Exception $e) {
            Log::error("Error limpiando horarios existentes: " . $e->getMessage());
            throw new \Exception("Error al limpiar horarios existentes: " . $e->getMessage());
        }
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

    public static function obtenerHorarioCarrera(int $carreraId, int $semestre, string $paralelo, int $campusId, int $periodoAcademicoId): Collection
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
