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
        for ($i = 0; $i < $this->cantidad_horas; $i++) {
            $fin = $inicio->copy()->addMinutes($this->duracion_hora);
            $horarios[] = [
                'inicio' => $inicio->format('H:i'),
                'fin' => $fin->format('H:i'),
            ];
            $inicio = $fin;
        }
        return $horarios;
    }

    // Mutator para guardar hora_inicio en formato H:i
    public function setHoraInicioAttribute($value)
    {
        $this->attributes['hora_inicio'] = \Carbon\Carbon::parse($value)->format('H:i');
    }

    // Mutator para nombre en minúsculas
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = strtolower($value);
    }

    // Relación ejemplo: si una jornada tiene muchos horarios generados (opcional)
    // public function horarios()
    // {
    //     return $this->hasMany(Horario::class, 'jornada_id');
    // }
}
