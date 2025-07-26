<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo',
        'duracion_semestres',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'duracion_semestres' => 'integer',
    ];

    // Relaciones
    public function campus()
    {
        return $this->belongsToMany(Campus::class, 'campus_carreras')
            ->withPivot('activa')
            ->withTimestamps();
    }

    public function campusActivos()
    {
        return $this->belongsToMany(Campus::class, 'campus_carreras')
            ->wherePivot('activa', true)
            ->withTimestamps();
    }

    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class);
    }

    public function asignaturasActivas()
    {
        return $this->hasMany(Asignatura::class)->where('activa', true);
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    public function distributivosAcademicos()
    {
        return $this->hasMany(DistributivoAcademico::class);
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

    // Mutadores
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtoupper($value);
    }

    public function setCodigoAttribute($value)
    {
        $this->attributes['codigo'] = strtoupper($value);
    }

    // Métodos auxiliares
    public function asignaturasPorSemestre($semestre)
    {
        return $this->asignaturas()->where('semestre', $semestre)->get();
    }
}
