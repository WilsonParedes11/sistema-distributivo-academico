<?php
// app/Services/HorarioGeneratorService.php

namespace App\Services;

use App\Models\DistributivoAcademico;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\PeriodoAcademico;
use App\Models\Jornada;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HorarioGeneratorService
{
    private array $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    private array $diasSemanaIntensiva = ['sabado'];

    // ...existing code...

    // Máximo de horas por día para una materia
    private const MAX_HORAS_POR_DIA_MATERIA = 2;

    // Cache para aulas asignadas por semestre
    private array $aulasAsignadasPorSemestre = [];

    public function generarHorarios(int $periodoAcademicoId, ?int $campusId = null, ?int $carreraId = null): array
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

            // Obtener distributivos del período con filtros específicos
            $query = DistributivoAcademico::where('periodo_academico_id', $periodoAcademicoId)
                ->where('activo', true)
                ->with(['docente.user', 'asignatura', 'carrera', 'campus']);

            // Aplicar filtros opcionales
            if ($campusId) {
                $query->where('campus_id', $campusId);
                Log::info("Filtrando por campus ID: {$campusId}");
            }

            if ($carreraId) {
                $query->where('carrera_id', $carreraId);
                Log::info("Filtrando por carrera ID: {$carreraId}");
            }

            $distributivos = $query->get();

            if ($distributivos->isEmpty()) {
                $mensaje = "No se encontraron distributivos activos para el período académico";
                if ($campusId)
                    $mensaje .= " en el campus seleccionado";
                if ($carreraId)
                    $mensaje .= " en la carrera seleccionada";

                $resultado['mensajes'][] = $mensaje;
                return $resultado;
            }

            Log::info("Encontrados " . $distributivos->count() . " distributivos para procesar");

            // Validar que hay aulas disponibles
            $this->validarAulasDisponibles($distributivos, $campusId ? [$campusId] : []);

            // Limpiar horarios existentes del período
            $this->limpiarHorariosExistentes($periodoAcademicoId, $campusId, $carreraId);

            // Inicializar cache de aulas por semestre - CORREGIDO
            $this->inicializarAulasPorSemestre($distributivos);

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

    private function inicializarAulasPorSemestre(Collection $distributivos): void
    {
        // Agrupar por campus, carrera, semestre y paralelo - TODOS los semestres
        $grupos = $distributivos->groupBy(function ($dist) {
            return "{$dist->campus_id}_{$dist->carrera_id}_{$dist->semestre}_{$dist->paralelo}";
        });

        Log::info("Inicializando aulas para " . $grupos->count() . " grupos de semestre/paralelo");

        foreach ($grupos as $key => $distribsGrupo) {
            $primerDist = $distribsGrupo->first();

            // Seleccionar una aula específica para cada grupo de semestre/paralelo
            $aula = $this->seleccionarAulaParaSemestre(
                $primerDist->campus,
                $primerDist->jornada,
                $primerDist->semestre,
                $primerDist->paralelo
            );

            if ($aula) {
                $this->aulasAsignadasPorSemestre[$key] = $aula;
                Log::info("Aula {$aula->codigo} asignada para semestre {$primerDist->semestre}, carrera {$primerDist->carrera->nombre}, paralelo {$primerDist->paralelo}");
            } else {
                Log::warning("No se pudo asignar aula para semestre {$primerDist->semestre}, carrera {$primerDist->carrera->nombre}, paralelo {$primerDist->paralelo}");
            }
        }
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

        // Ordenar docentes por carga académica (mayor a menor)
        $docentesOrdenados = $distributivosPorDocente->sortByDesc('carga_total');

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
                            'semestre' => $distributivo->semestre,
                            'paralelo' => $distributivo->paralelo,
                            'razon' => 'No se pudieron generar horarios para este distributivo con la carga actual'
                        ];
                        continue;
                    }

                    $horariosValidosCreados = 0;
                    foreach ($horarios as $horario) {
                        try {
                            Horario::create($horario);
                            $horariosGlobalesAsignados[] = $horario;
                            $horariosValidosCreados++;
                            $resultado['exitosos']++;
                        } catch (\Exception $e) {
                            Log::error("Error creando horario: " . $e->getMessage());
                        }
                    }

                    if ($horariosValidosCreados > 0) {
                        $resultado['mensajes'][] = "Horarios generados para {$docenteData['docente']->user->nombre_completo} - {$distributivo->asignatura->nombre} (Sem.{$distributivo->semestre}, Par.{$distributivo->paralelo}): {$horariosValidosCreados} horarios";
                    }

                } catch (\Exception $e) {
                    $resultado['errores']++;
                    $resultado['conflictos'][] = [
                        'docente' => $docenteData['docente']->user->nombre_completo,
                        'asignatura' => $distributivo->asignatura->nombre,
                        'semestre' => $distributivo->semestre,
                        'paralelo' => $distributivo->paralelo,
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
        $horasClase = $distributivo->horas_clase_semana;
        $horasPracticas = $distributivo->horas_componente_practico;
        $horasTeoricas = $horasClase - $horasPracticas;
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

        // Obtener el aula para este distributivo
        $aulaAsignada = $this->obtenerAulaParaDistributivo($distributivo);
        if (!$aulaAsignada) {
            throw new \Exception("No se encontró aula disponible para la asignatura {$distributivo->asignatura->nombre}");
        }

        $horariosGenerados = [];
        $diasValidos = $jornada === 'intensiva' ? $this->diasSemanaIntensiva : $this->diasSemana;

        // Buscar los mejores días disponibles para este docente
        $diasDisponiblesParaDocente = $this->encontrarDiasDisponiblesParaDocente($distributivo, $diasValidos, $horariosExistentes);

        // Calcular cuántos días necesitamos basado en el máximo de horas por día
        $diasNecesarios = ceil($horasClase / self::MAX_HORAS_POR_DIA_MATERIA);

        // Verificar que tenemos suficientes días disponibles
        if (count($diasDisponiblesParaDocente) < $diasNecesarios) {
            throw new \Exception("No hay suficientes días disponibles para asignar {$horasClase} horas a {$distributivo->asignatura->nombre}. Días necesarios: {$diasNecesarios}, Días disponibles: " . count($diasDisponiblesParaDocente));
        }

        // Distribución de horas: máximo 2 horas por día
        $horasRestantes = $horasClase;
        $horasPracticasRestantes = $horasPracticas;

        // Usar todos los días disponibles, no limitarnos solo a los necesarios
        $diasParaUsar = $diasDisponiblesParaDocente;

        foreach ($diasParaUsar as $diaInfo) {
            if ($horasRestantes <= 0)
                break;

            $dia = $diaInfo['dia'];

            // Calcular cuántas horas asignar en este día
            $horasParaEsteDia = min(self::MAX_HORAS_POR_DIA_MATERIA, $horasRestantes);

            // Intentar asignar horas en este día
            $horariosDelDia = $this->asignarHorasEnDia(
                $dia,
                $horasParaEsteDia,
                $horariosDisponibles,
                $horariosExistentes,
                $horariosGenerados,
                $distributivo,
                $aulaAsignada,
                $horasPracticasRestantes
            );

            if (!empty($horariosDelDia)) {
                $horariosGenerados = array_merge($horariosGenerados, $horariosDelDia);
                $horasRestantes -= count($horariosDelDia);

                Log::info("Asignadas " . count($horariosDelDia) . " horas en {$dia} para {$distributivo->asignatura->nombre}. Horas restantes: {$horasRestantes}");
            } else {
                Log::warning("No se pudieron asignar horas en {$dia} para {$distributivo->asignatura->nombre}");
            }
        }

        // Si aún faltan horas, intentar con una estrategia más agresiva
        if ($horasRestantes > 0) {
            Log::warning("Intentando estrategia alternativa para asignar {$horasRestantes} horas restantes para {$distributivo->asignatura->nombre}");

            // Intentar buscar cualquier espacio disponible sin restricción de máximo por día
            foreach ($diasParaUsar as $diaInfo) {
                if ($horasRestantes <= 0)
                    break;

                $dia = $diaInfo['dia'];

                // Intentar asignar una hora más en este día, sin importar cuántas ya tenga
                $horariosDelDia = $this->asignarHorasEnDia(
                    $dia,
                    min(1, $horasRestantes), // Solo una hora a la vez
                    $horariosDisponibles,
                    $horariosExistentes,
                    $horariosGenerados,
                    $distributivo,
                    $aulaAsignada,
                    $horasPracticasRestantes
                );

                if (!empty($horariosDelDia)) {
                    $horariosGenerados = array_merge($horariosGenerados, $horariosDelDia);
                    $horasRestantes -= count($horariosDelDia);

                    Log::info("Asignada 1 hora emergencia en {$dia} para {$distributivo->asignatura->nombre}. Horas restantes: {$horasRestantes}");
                }
            }
        }

        // Si todavía faltan horas, intentar en TODOS los días disponibles otra vez
        if ($horasRestantes > 0) {
            Log::warning("Último intento: buscando espacios en cualquier día para {$horasRestantes} horas de {$distributivo->asignatura->nombre}");

            // Recalcular días disponibles
            $todosLosDiasDisponibles = $this->encontrarDiasDisponiblesParaDocente($distributivo, $diasValidos, array_merge($horariosExistentes, $horariosGenerados));

            foreach ($todosLosDiasDisponibles as $diaInfo) {
                if ($horasRestantes <= 0)
                    break;

                $dia = $diaInfo['dia'];

                if ($diaInfo['disponibilidad'] > 0) {
                    $horariosDelDia = $this->asignarHorasEnDia(
                        $dia,
                        min($diaInfo['disponibilidad'], $horasRestantes),
                        $horariosDisponibles,
                        $horariosExistentes,
                        $horariosGenerados,
                        $distributivo,
                        $aulaAsignada,
                        $horasPracticasRestantes
                    );

                    if (!empty($horariosDelDia)) {
                        $horariosGenerados = array_merge($horariosGenerados, $horariosDelDia);
                        $horasRestantes -= count($horariosDelDia);

                        Log::info("Último intento exitoso: asignadas " . count($horariosDelDia) . " horas en {$dia} para {$distributivo->asignatura->nombre}. Horas restantes: {$horasRestantes}");
                    }
                }
            }
        }

        // Verificar que se asignaron todas las horas requeridas
        if (count($horariosGenerados) !== $horasClase) {
            throw new \Exception("No se asignaron todas las horas requeridas para {$distributivo->asignatura->nombre}. Horas asignadas: " . count($horariosGenerados) . ", Horas requeridas: {$horasClase}");
        }

        // Verificar horas prácticas - contar las que realmente se asignaron
        $horasPracticasAsignadas = collect($horariosGenerados)->where('tipo_clase', 'practica')->count();
        if ($horasPracticasAsignadas !== $horasPracticas) {
            // Log de debug para ver qué pasó
            Log::error("Error en horas prácticas para {$distributivo->asignatura->nombre}:");
            Log::error("Horas prácticas requeridas: {$horasPracticas}");
            Log::error("Horas prácticas asignadas: {$horasPracticasAsignadas}");
            Log::error("Horarios generados: " . json_encode(array_map(function ($h) {
                return [
                    'dia' => $h['dia_semana'],
                    'inicio' => $h['hora_inicio'],
                    'tipo' => $h['tipo_clase']
                ];
            }, $horariosGenerados)));

            throw new \Exception("No se asignaron las horas prácticas requeridas para {$distributivo->asignatura->nombre}. Horas prácticas asignadas: {$horasPracticasAsignadas}, Horas prácticas requeridas: {$horasPracticas}");
        }

        Log::info("Horarios generados exitosamente para {$distributivo->asignatura->nombre}: " . count($horariosGenerados) . " horas ({$horasPracticasAsignadas} prácticas, " . (count($horariosGenerados) - $horasPracticasAsignadas) . " teóricas)");

        return $horariosGenerados;
    }

    private function asignarHorasEnDia(string $dia, int $horasRequeridas, array $horariosDisponibles, array $horariosExistentes, array $horariosGenerados, DistributivoAcademico $distributivo, Aula $aula, int &$horasPracticasRestantes): array
    {
        $horariosDelDia = [];
        $horasAsignadas = 0;

        // Obtener todos los horarios ya ocupados en este día para este docente
        $horariosOcupadosDocente = [];
        $todosLosHorarios = array_merge($horariosExistentes, $horariosGenerados);

        foreach ($todosLosHorarios as $horario) {
            if ($horario['dia_semana'] === $dia) {
                $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                if ($otroDistributivo && $otroDistributivo->docente_id === $distributivo->docente_id) {
                    $horariosOcupadosDocente[] = $horario['hora_inicio'] . '-' . $horario['hora_fin'];
                }
            }
        }

        Log::debug("Día {$dia} - {$distributivo->asignatura->nombre}: Horarios ocupados del docente: " . implode(', ', $horariosOcupadosDocente));

        // Intentar asignar horarios individuales
        foreach ($horariosDisponibles as $horarioSlot) {
            if ($horasAsignadas >= $horasRequeridas)
                break;

            $slotKey = $horarioSlot['inicio'] . '-' . $horarioSlot['fin'];

            // Verificar si este slot está disponible para el docente
            if (
                $this->estaHorarioDisponibleParaDocente($dia, $horarioSlot['inicio'], $horarioSlot['fin'], $horariosExistentes, $horariosGenerados, $distributivo) &&
                $this->estaAulaDisponible($aula, $dia, $horarioSlot['inicio'], $horarioSlot['fin'], $horariosExistentes, $horariosGenerados)
            ) {
                // Priorizar horas prácticas primero
                $tipoClase = ($horasPracticasRestantes > 0) ? 'practica' : 'teorica';

                $horarioNuevo = [
                    'distributivo_academico_id' => $distributivo->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => $horarioSlot['inicio'],
                    'hora_fin' => $horarioSlot['fin'],
                    'tipo_clase' => $tipoClase,
                    'aula' => $aula->codigo,
                    'edificio' => $aula->edificio,
                ];

                $horariosDelDia[] = $horarioNuevo;

                if ($tipoClase === 'practica') {
                    $horasPracticasRestantes--;
                }

                $horasAsignadas++;
                Log::debug("Asignado: {$dia} {$horarioSlot['inicio']}-{$horarioSlot['fin']} ({$tipoClase}) para {$distributivo->asignatura->nombre}");
            } else {
                Log::debug("No disponible: {$dia} {$horarioSlot['inicio']}-{$horarioSlot['fin']} para {$distributivo->asignatura->nombre}");
            }
        }

        // Debug: log de lo que se asignó
        if (!empty($horariosDelDia)) {
            $practicas = array_filter($horariosDelDia, fn($h) => $h['tipo_clase'] === 'practica');
            $teoricas = array_filter($horariosDelDia, fn($h) => $h['tipo_clase'] === 'teorica');

            Log::debug("Día {$dia} - {$distributivo->asignatura->nombre}: " .
                count($practicas) . " prácticas, " .
                count($teoricas) . " teóricas. " .
                "Prácticas restantes: {$horasPracticasRestantes}");
        } else {
            Log::warning("No se pudieron asignar horas en {$dia} para {$distributivo->asignatura->nombre} - Sin horarios disponibles");
        }

        return $horariosDelDia;
    }

    private function obtenerAulaParaDistributivo(DistributivoAcademico $distributivo): ?Aula
    {
        // Usar el aula pre-asignada para este grupo específico de semestre/paralelo
        $key = "{$distributivo->campus_id}_{$distributivo->carrera_id}_{$distributivo->semestre}_{$distributivo->paralelo}";

        $aula = $this->aulasAsignadasPorSemestre[$key] ?? null;

        if (!$aula) {
            Log::warning("No se encontró aula pre-asignada para {$key}, buscando aula disponible");
            // Fallback: buscar cualquier aula disponible
            $aula = $this->seleccionarAulaParaSemestre(
                $distributivo->campus,
                $distributivo->jornada,
                $distributivo->semestre,
                $distributivo->paralelo
            );
        }

        return $aula;
    }

    private function seleccionarAulaParaSemestre($campus, string $jornada, int $semestre, string $paralelo): ?Aula
    {
        // Obtener todas las aulas activas del campus
        $aulasDisponibles = Aula::where('campus_id', $campus->id)
            ->where('activa', true)
            ->get();

        if ($aulasDisponibles->isEmpty()) {
            Log::warning("No se encontraron aulas disponibles en campus {$campus->id}");
            return null;
        }

        // Obtener aulas ya asignadas para evitar duplicados en el mismo horario
        $aulasYaAsignadas = collect($this->aulasAsignadasPorSemestre)->pluck('id')->toArray();

        // Priorizar aulas no asignadas
        $aulaNoAsignada = $aulasDisponibles->whereNotIn('id', $aulasYaAsignadas)->first();

        if ($aulaNoAsignada) {
            Log::info("Asignando aula nueva {$aulaNoAsignada->codigo} para semestre {$semestre}, paralelo {$paralelo}");
            return $aulaNoAsignada;
        }

        // Si no hay aulas nuevas disponibles, usar cualquiera disponible
        // pero verificar que no cause conflictos de horario
        foreach ($aulasDisponibles as $aula) {
            if ($this->puedeUsarAula($aula, $jornada)) {
                Log::info("Reutilizando aula {$aula->codigo} para semestre {$semestre}, paralelo {$paralelo}");
                return $aula;
            }
        }

        Log::warning("No se encontró aula adecuada para semestre {$semestre}, paralelo {$paralelo} en jornada {$jornada}");
        return $aulasDisponibles->first(); // Última opción
    }

    private function puedeUsarAula(Aula $aula, string $jornada): bool
    {
        // Aquí podrías implementar lógica adicional para verificar
        // si el aula es adecuada para la jornada específica
        // Por ejemplo, algunas aulas podrían estar reservadas para ciertas jornadas

        return true; // Por ahora, cualquier aula puede usarse en cualquier jornada
    }

    private function encontrarDiasDisponiblesParaDocente(DistributivoAcademico $distributivo, array $diasValidos, array $horariosExistentes): array
    {
        $disponibilidadPorDia = [];
        $docenteId = $distributivo->docente_id;
        $maxHorariosPorDia = count($this->obtenerHorariosPorJornada($distributivo->jornada));

        foreach ($diasValidos as $dia) {
            $horariosOcupadosDocente = 0;

            // Contar horarios ocupados por el docente en este día
            foreach ($horariosExistentes as $horario) {
                if ($horario['dia_semana'] === $dia) {
                    $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                    if ($otroDistributivo && $otroDistributivo->docente_id === $docenteId) {
                        $horariosOcupadosDocente++;
                    }
                }
            }

            $disponibilidad = $maxHorariosPorDia - $horariosOcupadosDocente;

            $disponibilidadPorDia[] = [
                'dia' => $dia,
                'horarios_ocupados' => $horariosOcupadosDocente,
                'disponibilidad' => max(0, $disponibilidad)
            ];
        }

        // Ordenar por disponibilidad descendente para priorizar días con más espacios libres
        usort($disponibilidadPorDia, function ($a, $b) {
            if ($a['disponibilidad'] === $b['disponibilidad']) {
                return strcmp($a['dia'], $b['dia']);
            }
            return $b['disponibilidad'] <=> $a['disponibilidad'];
        });

        // Incluir todos los días que tienen al menos alguna disponibilidad
        $diasConDisponibilidad = array_filter($disponibilidadPorDia, function ($dia) {
            return $dia['disponibilidad'] > 0;
        });

        Log::info("Días disponibles para docente {$distributivo->docente->user->nombre_completo} - {$distributivo->asignatura->nombre}: " .
            implode(', ', array_map(function ($d) {
                return $d['dia'] . '(' . $d['disponibilidad'] . ')';
            }, $diasConDisponibilidad)));

        return array_values($diasConDisponibilidad);
    }

    private function estaHorarioDisponibleParaDocente(string $dia, string $inicio, string $fin, array $horariosExistentes, array $horariosGenerados, DistributivoAcademico $distributivo): bool
    {
        $docenteId = $distributivo->docente_id;
        $todosLosHorarios = array_merge($horariosExistentes, $horariosGenerados);

        foreach ($todosLosHorarios as $horario) {
            if ($horario['dia_semana'] === $dia) {
                $otroDistributivo = DistributivoAcademico::find($horario['distributivo_academico_id']);
                if ($otroDistributivo && $otroDistributivo->docente_id === $docenteId) {
                    if ($this->hayConflictoHorario($inicio, $fin, $horario['hora_inicio'], $horario['hora_fin'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function estaAulaDisponible(Aula $aula, string $dia, string $inicio, string $fin, array $horariosExistentes, array $horariosGenerados): bool
    {
        $todosLosHorarios = array_merge($horariosExistentes, $horariosGenerados);

        foreach ($todosLosHorarios as $horario) {
            if (
                $horario['dia_semana'] === $dia &&
                isset($horario['aula']) &&
                $horario['aula'] === $aula->codigo
            ) {
                if ($this->hayConflictoHorario($inicio, $fin, $horario['hora_inicio'], $horario['hora_fin'])) {
                    return false;
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


    private function obtenerHorariosPorJornada(string $jornada): array
    {
        $config = Jornada::where('nombre', $jornada)->first();
        if (!$config) {
            throw new \Exception("No existe configuración para la jornada: {$jornada}");
        }

        $horarios = [];
        $inicio = Carbon::parse($config->hora_inicio);

        for ($i = 0; $i < $config->cantidad_horas; $i++) {
            $fin = $inicio->copy()->addMinutes($config->duracion_hora);
            $horarios[] = [
                'inicio' => $inicio->format('H:i'),
                'fin' => $fin->format('H:i')
            ];
            $inicio = $fin;
        }
        return $horarios;
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

            Log::info("Campus {$campusId}: {$aulasActivas} aulas activas disponibles");
        }
    }

    private function limpiarHorariosExistentes(int $periodoAcademicoId, ?int $campusId = null, ?int $carreraId = null): void
    {
        try {
            $query = Horario::whereHas('distributivoAcademico', function ($q) use ($periodoAcademicoId, $campusId, $carreraId) {
                $q->where('periodo_academico_id', $periodoAcademicoId);

                if ($campusId) {
                    $q->where('campus_id', $campusId);
                }

                if ($carreraId) {
                    $q->where('carrera_id', $carreraId);
                }
            });

            $eliminados = $query->count();
            $query->delete();

            $mensaje = "Eliminados {$eliminados} horarios existentes para el período {$periodoAcademicoId}";
            if ($campusId)
                $mensaje .= " en campus {$campusId}";
            if ($carreraId)
                $mensaje .= " en carrera {$carreraId}";

            Log::info($mensaje);
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
    })->with(['distributivoAcademico.asignatura', 'distributivoAcademico.carrera', 'distributivoAcademico.docente.user'])
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
