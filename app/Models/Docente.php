<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'titulo_profesional',
        'grado_ocupacional',
        'modalidad_trabajo',
        'fecha_vinculacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_vinculacion' => 'date',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function distributivosAcademicos()
    {
        return $this->hasMany(DistributivoAcademico::class);
    }

    public function asignaturas()
    {
        return $this->hasManyThrough(Asignatura::class, DistributivoAcademico::class);
    }

    public function horarios()
    {
        return $this->hasManyThrough(Horario::class, DistributivoAcademico::class);
    }

    public function periodoActual()
    {
        return $this->distributivosAcademicos()
            ->whereHas('periodoAcademico', function ($query) {
                $query->where('activo', true);
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

    public function getAntiguedadAttribute()
    {
        return $this->fecha_vinculacion->diffInYears(now());
    }

    public function getAntiguedadMesesAttribute()
    {
        return $this->fecha_vinculacion->diffInMonths(now());
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true)
            ->whereHas('user', function ($q) {
                $q->where('activo', true);
            });
    }

    public function scopePorModalidad($query, $modalidad)
    {
        return $query->where('modalidad_trabajo', $modalidad);
    }

    public function scopePorGrado($query, $grado)
    {
        return $query->where('grado_ocupacional', $grado);
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
        })->orWhere('titulo_profesional', 'LIKE', "%{$term}%");
    }

    // Mutadores
    public function setTituloProfesionalAttribute($value)
    {
        $this->attributes['titulo_profesional'] = strtoupper(trim($value));
    }

    // Métodos auxiliares
    public function totalHorasActuales()
    {
        return $this->periodoActual()->sum('total_horas_semanales');
    }

    public function asignaturasPorPeriodo($periodoId)
    {
        return $this->distributivosAcademicos()
            ->where('periodo_academico_id', $periodoId)
            ->with(['asignatura', 'carrera', 'campus'])
            ->get();
    }

    public function campusAsignados()
    {
        return Campus::whereIn(
            'id',
            $this->distributivosAcademicos()
                ->distinct()
                ->pluck('campus_id')
        )->get();
    }

    public function carrerasAsignadas()
    {
        return Carrera::whereIn(
            'id',
            $this->distributivosAcademicos()
                ->distinct()
                ->pluck('carrera_id')
        )->get();
    }

    public function esTiempoCompleto()
    {
        return $this->modalidad_trabajo === 'TC';
    }

    public function esMedioTiempo()
    {
        return $this->modalidad_trabajo === 'MT';
    }

    public function puedeAsignarHoras($horas)
    {
        $horasActuales = $this->totalHorasActuales();
        $maxHoras = $this->esTiempoCompleto() ? 40 : 20;

        return ($horasActuales + $horas) <= $maxHoras;
    }

    public function horasDisponibles()
    {
        $horasActuales = $this->totalHorasActuales();
        $maxHoras = $this->esTiempoCompleto() ? 40 : 20;

        return max(0, $maxHoras - $horasActuales);
    }
}
