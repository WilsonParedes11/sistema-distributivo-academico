<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aula extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'codigo',
        'campus_id',
        'carrera_id',
        'edificio',
        'capacidad',
        'tipo',
        'recursos_disponibles',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'capacidad' => 'integer',
        'recursos_disponibles' => 'array',
    ];

    // Relaciones
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'aula', 'codigo');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    public function scopeConCapacidad($query, $capacidadMinima)
    {
        return $query->where('capacidad', '>=', $capacidadMinima);
    }

    // Mutadores
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }

    public function setCodigoAttribute($value)
    {
        $this->attributes['codigo'] = strtoupper($value);
    }

    // Accessors
    public function getIdentificadorCompletoAttribute()
    {
        return $this->campus->codigo . '-' . $this->codigo;
    }

    // Métodos auxiliares
    public function estaDisponible($dia, $horaInicio, $horaFin)
    {
        return !$this->horarios()
            ->where('dia_semana', $dia)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                    ->orWhereBetween('hora_fin', [$horaInicio, $horaFin])
                    ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                        $q->where('hora_inicio', '<=', $horaInicio)
                            ->where('hora_fin', '>=', $horaFin);
                    });
            })->exists();
    }

    public function tieneRecurso($recurso)
    {
        return in_array($recurso, $this->recursos_disponibles ?? []);
    }

    public function horariosSemana()
    {
        return $this->horarios()
            ->with(['distributivoAcademico.asignatura', 'distributivoAcademico.docente'])
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('dia_semana');
    }
}
