<?php
// app/Models/Estudiante.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'codigo_estudiante',
        'carrera_id',
        'campus_id',
        'semestre_actual',
        'paralelo',
        'jornada',
        'fecha_ingreso',
        'estado',
    ];

    protected $casts = [
        'semestre_actual' => 'integer',
        'fecha_ingreso' => 'date',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function horariosCarrera()
    {
        return Horario::whereHas('distributivoAcademico', function ($query) {
            $query->where('carrera_id', $this->carrera_id)
                ->where('semestre', $this->semestre_actual)
                ->where('paralelo', $this->paralelo)
                ->where('campus_id', $this->campus_id)
                ->where('jornada', $this->jornada);
        });
    }

    // Accessors que delegan al usuario
    public function getCedulaAttribute()
    {
        return $this->user->cedula;
    }

    public function getNombresAttribute()
    {
        return $this->user->nombres;
    }

    public function getApellidosAttribute()
    {
        return $this->user->apellidos;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getTelefonoAttribute()
    {
        return $this->user->telefono;
    }

    public function getNombreCompletoAttribute()
    {
        return $this->user->nombre_completo;
    }

    public function getTiempoEstudiosAttribute()
    {
        return $this->fecha_ingreso->diffInMonths(now());
    }

    public function getTiempoEstudiosAniosAttribute()
    {
        return $this->fecha_ingreso->diffInYears(now());
    }

    public function getIdentificadorCompletoAttribute()
    {
        return $this->carrera->codigo . '-' . $this->semestre_actual . $this->paralelo . '-' . $this->codigo_estudiante;
    }

    public function getProgresoCarreraAttribute()
    {
        $porcentaje = ($this->semestre_actual / $this->carrera->duracion_semestres) * 100;
        return min(100, $porcentaje);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo')
            ->whereHas('user', function ($q) {
                $q->where('activo', true);
            });
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre_actual', $semestre);
    }

    public function scopePorParalelo($query, $paralelo)
    {
        return $query->where('paralelo', $paralelo);
    }

    public function scopePorJornada($query, $jornada)
    {
        return $query->where('jornada', $jornada);
    }

    public function scopeConUsuario($query)
    {
        return $query->with('user');
    }

    public function scopeSearch($query, $term)
    {
        if (!$term)
            return $query;

        return $query->whereHas('user', function ($q) use ($term) {
            $q->where('nombres', 'LIKE', "%{$term}%")
                ->orWhere('apellidos', 'LIKE', "%{$term}%")
                ->orWhere('cedula', 'LIKE', "%{$term}%");
        })->orWhere('codigo_estudiante', 'LIKE', "%{$term}%");
    }

    // Mutadores
    public function setCodigoEstudianteAttribute($value)
    {
        $this->attributes['codigo_estudiante'] = strtoupper(trim($value));
    }

    public function setParaleloAttribute($value)
    {
        $this->attributes['paralelo'] = strtoupper(trim($value));
    }

    // Métodos auxiliares
    public function misHorarios($periodoId = null)
    {
        $query = $this->horariosCarrera();

        if ($periodoId) {
            $query->whereHas('distributivoAcademico', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }

        return $query->with([
            'distributivoAcademico.asignatura',
            'distributivoAcademico.docente.user',
            'aulaRelacion'
        ])->orderBy('dia_semana')->orderBy('hora_inicio')->get();
    }

    public function asignaturasActuales()
    {
        return Asignatura::where('carrera_id', $this->carrera_id)
            ->where('semestre', $this->semestre_actual)
            ->where('activa', true)
            ->get();
    }

    public function compañerosClase()
    {
        return static::where('carrera_id', $this->carrera_id)
            ->where('campus_id', $this->campus_id)
            ->where('semestre_actual', $this->semestre_actual)
            ->where('paralelo', $this->paralelo)
            ->where('jornada', $this->jornada)
            ->where('estado', 'activo')
            ->where('id', '!=', $this->id)
            ->with('user')
            ->get();
    }

    public function avanzarSemestre()
    {
        if ($this->semestre_actual < $this->carrera->duracion_semestres) {
            $this->increment('semestre_actual');
            return true;
        }
        return false;
    }

    public function graduarse()
    {
        if ($this->semestre_actual >= $this->carrera->duracion_semestres) {
            $this->update(['estado' => 'graduado']);
            return true;
        }
        return false;
    }

    public function puedeAvanzar()
    {
        return $this->semestre_actual < $this->carrera->duracion_semestres;
    }

    public function puedeGraduarse()
    {
        return $this->semestre_actual >= $this->carrera->duracion_semestres;
    }

    public function generarCodigoEstudiante()
    {
        $anio = $this->fecha_ingreso->format('Y');
        $carrera = $this->carrera->codigo;
        $correlativo = str_pad($this->id, 4, '0', STR_PAD_LEFT);

        return $anio . $carrera . $correlativo;
    }

    public static function siguienteCodigoEstudiante($carreraId, $anio = null)
    {
        $anio = $anio ?? now()->year;
        $carrera = Carrera::find($carreraId);

        $ultimo = static::where('codigo_estudiante', 'LIKE', $anio . $carrera->codigo . '%')
            ->orderBy('codigo_estudiante', 'desc')
            ->first();

        if ($ultimo) {
            $numero = intval(substr($ultimo->codigo_estudiante, -4)) + 1;
        } else {
            $numero = 1;
        }

        return $anio . $carrera->codigo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}
