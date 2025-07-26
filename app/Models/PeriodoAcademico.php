<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasFactory;

    protected $table = 'periodos_academicos';

    protected $fillable = [
        'nombre',
        'año',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'año' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Relaciones
    public function distributivosAcademicos()
    {
        return $this->hasMany(DistributivoAcademico::class);
    }

    public function docentes()
    {
        return $this->hasManyThrough(Docente::class, DistributivoAcademico::class);
    }

    public function asignaturas()
    {
        return $this->hasManyThrough(Asignatura::class, DistributivoAcademico::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorAño($query, $año)
    {
        return $query->where('año', $año);
    }

    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    // Métodos auxiliares
    public static function periodoActivo()
    {
        return static::where('activo', true)->first();
    }

    public function activar()
    {
        // Desactivar todos los períodos
        static::where('activo', true)->update(['activo' => false]);

        // Activar este período
        $this->update(['activo' => true]);
    }

    public function estaVigente()
    {
        $hoy = now()->toDateString();
        return $hoy >= $this->fecha_inicio && $hoy <= $this->fecha_fin;
    }

    public function distributivoPorCampus($campusId)
    {
        return $this->distributivosAcademicos()
            ->where('campus_id', $campusId)
            ->with(['docente', 'asignatura', 'carrera'])
            ->get();
    }
}
