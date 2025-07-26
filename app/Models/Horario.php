<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributivo_academico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'aula',
        'edificio',
        'tipo_clase',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    // Relaciones
    public function distributivoAcademico()
    {
        return $this->belongsTo(DistributivoAcademico::class);
    }

    public function docente()
    {
        return $this->hasOneThrough(Docente::class, DistributivoAcademico::class);
    }

    public function asignatura()
    {
        return $this->hasOneThrough(Asignatura::class, DistributivoAcademico::class);
    }

    public function carrera()
    {
        return $this->hasOneThrough(Carrera::class, DistributivoAcademico::class);
    }

    public function campus()
    {
        return $this->hasOneThrough(Campus::class, DistributivoAcademico::class);
    }

    public function aulaRelacion()
    {
        return $this->belongsTo(Aula::class, 'aula', 'codigo');
    }

    // Scopes
    public function scopePorDia($query, $dia)
    {
        return $query->where('dia_semana', $dia);
    }

    public function scopePorHorario($query, $horaInicio, $horaFin)
    {
        return $query->where('hora_inicio', '>=', $horaInicio)
            ->where('hora_fin', '<=', $horaFin);
    }

    public function scopePorAula($query, $aula)
    {
        return $query->where('aula', $aula);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_clase', $tipo);
    }

    // Accessors
    public function getDuracionHorasAttribute()
    {
        $inicio = \Carbon\Carbon::parse($this->hora_inicio);
        $fin = \Carbon\Carbon::parse($this->hora_fin);
        return $fin->diffInHours($inicio);
    }

    public function getRangoHorarioAttribute()
    {
        return $this->hora_inicio . ' - ' . $this->hora_fin;
    }

    // Métodos auxiliares
    public function verificarConflicto($dia, $horaInicio, $horaFin, $aula = null)
    {
        $query = static::where('dia_semana', $dia)
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                    ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                    ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                        $q2->where('hora_inicio', '<=', $horaInicio)
                            ->where('hora_fin', '>=', $horaFin);
                    });
            });

        if ($aula) {
            $query->where('aula', $aula);
        }

        return $query->exists();
    }

    public static function horariosPorDocente($docenteId, $periodoId = null)
    {
        $query = static::whereHas('distributivoAcademico', function ($q) use ($docenteId, $periodoId) {
            $q->where('docente_id', $docenteId);
            if ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            }
        });

        return $query->orderBy('dia_semana')->orderBy('hora_inicio')->get();
    }

    public static function horariosPorCarrera($carreraId, $semestre = null, $paralelo = null)
    {
        $query = static::whereHas('distributivoAcademico', function ($q) use ($carreraId, $semestre, $paralelo) {
            $q->where('carrera_id', $carreraId);
            if ($semestre) {
                $q->where('semestre', $semestre);
            }
            if ($paralelo) {
                $q->where('paralelo', $paralelo);
            }
        });

        return $query->orderBy('dia_semana')->orderBy('hora_inicio')->get();
    }
}
