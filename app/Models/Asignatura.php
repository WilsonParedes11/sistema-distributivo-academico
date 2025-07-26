<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asignatura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'codigo',
        'carrera_id',
        'semestre',
        'horas_semanales',
        'horas_practicas',
        'creditos',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'semestre' => 'integer',
        'horas_semanales' => 'integer',
        'horas_practicas' => 'integer',
        'creditos' => 'integer',
    ];

    // Relaciones
    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function distributivosAcademicos()
    {
        return $this->hasMany(DistributivoAcademico::class);
    }

    public function horarios()
    {
        return $this->hasManyThrough(Horario::class, DistributivoAcademico::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
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
    public function getTipoHorasAttribute()
    {
        $teoricas = $this->horas_semanales - $this->horas_practicas;
        return [
            'teoricas' => $teoricas,
            'practicas' => $this->horas_practicas,
            'total' => $this->horas_semanales
        ];
    }
}
