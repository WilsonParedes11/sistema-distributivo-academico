<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributivoAcademico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'distributivo_academico';

    protected $fillable = [
        'periodo_academico_id',
        'docente_id',
        'asignatura_id',
        'carrera_id',
        'campus_id',
        'paralelo',
        'semestre',
        'jornada',
        'horas_componente_practico',
        'horas_clase_semana',
        'horas_actividades_docencia',
        'horas_investigacion_semanal',
        'nombre_proyecto_investigacion',
        'horas_direccion_academica_semanal',
        'detalle_horas_direccion',
        'total_horas_semanales',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'semestre' => 'integer',
        'horas_componente_practico' => 'integer',
        'horas_clase_semana' => 'integer',
        'horas_actividades_docencia' => 'integer',
        'horas_investigacion_semanal' => 'integer',
        'horas_direccion_academica_semanal' => 'integer',
        'total_horas_semanales' => 'integer',
    ];

    // Relaciones
    public function periodoAcademico()
    {
        return $this->belongsTo(PeriodoAcademico::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorPeriodo($query, $periodoId)
    {
        return $query->where('periodo_academico_id', $periodoId);
    }

    public function scopePorDocente($query, $docenteId)
    {
        return $query->where('docente_id', $docenteId);
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    public function scopePorJornada($query, $jornada)
    {
        return $query->where('jornada', $jornada);
    }

    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }

    // Mutadores
    public function setParaleloAttribute($value)
    {
        $this->attributes['paralelo'] = strtoupper($value);
    }

    // Accessors
    public function getIdentificadorAttribute()
    {
        return $this->carrera->codigo . '-' . $this->semestre . $this->paralelo;
    }

    public function getHorasDistribucionAttribute()
    {
        return [
            'clase' => $this->horas_clase_semana,
            'docencia' => $this->horas_actividades_docencia,
            'investigacion' => $this->horas_investigacion_semanal,
            'direccion' => $this->horas_direccion_academica_semanal,
            'total' => $this->total_horas_semanales
        ];
    }

    // Métodos auxiliares
    public function tieneHorarios()
    {
        return $this->horarios()->exists();
    }

    public function generarHorarios($configuracion)
    {
        // Lógica para generar horarios automáticamente
        // basado en la configuración proporcionada
    }

    public function validarCargaHoraria()
    {
        $calculado = $this->horas_clase_semana +
            $this->horas_actividades_docencia +
            $this->horas_investigacion_semanal +
            $this->horas_direccion_academica_semanal;

        return $calculado === $this->total_horas_semanales;
    }
}
