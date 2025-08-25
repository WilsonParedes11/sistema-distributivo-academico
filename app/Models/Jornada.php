<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    protected $fillable = [
        'nombre',
        'hora_inicio',
        'cantidad_horas',
        'duracion_hora',
        'hora_inicio_receso',
        'hora_fin_receso',
    ];

    // Scope para buscar por nombre
    public function scopeNombre($query, $nombre)
    {
        return $query->where('nombre', $nombre);
    }

    // Accessor para hora_inicio como Carbon
    public function getHoraInicioCarbonAttribute()
    {
        return \Carbon\Carbon::parse($this->hora_inicio);
    }

    // Accessor para obtener los intervalos de horarios
    public function getIntervalosAttribute()
    {
        $horarios = [];
        $inicio = $this->hora_inicio_carbon;
        $receso_inicio = $this->hora_inicio_receso ? \Carbon\Carbon::parse($this->hora_inicio_receso) : null;
        $receso_fin = $this->hora_fin_receso ? \Carbon\Carbon::parse($this->hora_fin_receso) : null;

        $periodosGenerados = 0;

        while ($periodosGenerados < $this->cantidad_horas) {
            $fin = $inicio->copy()->addMinutes($this->duracion_hora);

            // Verificar si el período actual intersecta con el receso
            if ($receso_inicio && $receso_fin) {
                // Si el período empieza antes del receso y termina después de que empiece el receso
                if ($inicio->lt($receso_inicio) && $fin->gt($receso_inicio)) {
                    // Crear período hasta el inicio del receso
                    $horarios[] = [
                        'inicio' => $inicio->format('H:i'),
                        'fin' => $receso_inicio->format('H:i'),
                    ];
                    $periodosGenerados++;

                    // Continuar después del receso si aún faltan períodos
                    if ($periodosGenerados < $this->cantidad_horas) {
                        $inicio = $receso_fin->copy();
                        continue;
                    }
                    break;
                }
                // Si el período está completamente dentro del receso, saltar al final del receso
                elseif ($inicio->gte($receso_inicio) && $fin->lte($receso_fin)) {
                    $inicio = $receso_fin->copy();
                    continue;
                }
                // Si el período empieza durante el receso, mover al final del receso
                elseif ($inicio->gte($receso_inicio) && $inicio->lt($receso_fin)) {
                    $inicio = $receso_fin->copy();
                    continue;
                }
            }

            // Período normal (no intersecta con receso)
            $horarios[] = [
                'inicio' => $inicio->format('H:i'),
                'fin' => $fin->format('H:i'),
            ];

            $periodosGenerados++;
            $inicio = $fin;
        }

        return $horarios;
    }

    // Mutator para guardar hora_inicio en formato H:i
    public function setHoraInicioAttribute($value)
    {
        $this->attributes['hora_inicio'] = \Carbon\Carbon::parse($value)->format('H:i');
    }

    // Mutator para guardar hora_inicio_receso en formato H:i
    public function setHoraInicioRecesoAttribute($value)
    {
        $this->attributes['hora_inicio_receso'] = $value ? \Carbon\Carbon::parse($value)->format('H:i') : null;
    }

    // Mutator para guardar hora_fin_receso en formato H:i
    public function setHoraFinRecesoAttribute($value)
    {
        $this->attributes['hora_fin_receso'] = $value ? \Carbon\Carbon::parse($value)->format('H:i') : null;
    }

    // Mutator para nombre en minúsculas
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtolower($value);
    }

    // Método para verificar si la jornada tiene receso configurado
    public function tieneReceso(): bool
    {
        return !empty($this->hora_inicio_receso) && !empty($this->hora_fin_receso);
    }

    // Método para obtener la duración del receso en minutos
    public function getDuracionRecesoAttribute(): int
    {
        if (!$this->tieneReceso()) {
            return 0;
        }

        $inicio = \Carbon\Carbon::parse($this->hora_inicio_receso);
        $fin = \Carbon\Carbon::parse($this->hora_fin_receso);

        return $fin->diffInMinutes($inicio);
    }

    // Método para verificar si una hora específica está en el receso
    public function estaEnReceso(string $hora): bool
    {
        if (!$this->tieneReceso()) {
            return false;
        }

        $horaCheck = \Carbon\Carbon::parse($hora);
        $inicioReceso = \Carbon\Carbon::parse($this->hora_inicio_receso);
        $finReceso = \Carbon\Carbon::parse($this->hora_fin_receso);

        return $horaCheck->between($inicioReceso, $finReceso);
    }

    // Método para calcular la hora de fin real de la jornada
    public function getHoraFinJornadaAttribute()
    {
        $intervalos = $this->intervalos;

        if (empty($intervalos)) {
            // Si no hay intervalos, calcular basado en duración total
            $inicio = $this->hora_inicio_carbon;
            $duracionTotal = $this->cantidad_horas * $this->duracion_hora;

            // Agregar duración del receso si existe
            if ($this->tieneReceso()) {
                $duracionTotal += $this->duracion_receso;
            }

            return $inicio->copy()->addMinutes($duracionTotal);
        }

        // Tomar la hora de fin del último intervalo
        $ultimoIntervalo = end($intervalos);
        return \Carbon\Carbon::parse($ultimoIntervalo['fin']);
    }

    // Relación ejemplo: si una jornada tiene muchos horarios generados (opcional)
    // public function horarios()
    // {
    //     return $this->hasMany(Horario::class, 'jornada_id');
    // }
}
