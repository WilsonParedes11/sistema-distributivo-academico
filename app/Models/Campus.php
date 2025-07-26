<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $table = 'campus';

    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'campus_carreras')
            ->withPivot('activa')
            ->withTimestamps();
    }

    public function carrerasActivas()
    {
        return $this->belongsToMany(Carrera::class, 'campus_carreras')
            ->wherePivot('activa', true)
            ->withTimestamps();
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }

    public function aulasActivas()
    {
        return $this->hasMany(Aula::class)->where('activa', true);
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
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
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
}
