<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CampusCarrera extends Pivot
{
    protected $table = 'campus_carreras';

    protected $fillable = [
        'campus_id',
        'carrera_id',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public $timestamps = true;

    // Relaciones
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
