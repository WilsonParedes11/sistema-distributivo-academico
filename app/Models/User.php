<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'email',
        'password',
        'tipo_usuario',
        'telefono',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'full_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Get the user's name for Filament compatibility.
     * Filament expects a 'name' field but we use 'nombres'.
     */
    public function getNameAttribute(): string
    {
        return $this->nombres ?? '';
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return ($this->nombres ?? '') . ' ' . ($this->apellidos ?? '');
    }

    /**
     * Filament compatibility method for getting user name
     */
    public function getFilamentName(): string
    {
        return $this->nombres ?? $this->email ?? 'Usuario';
    }

    // Relaciones
    public function docente()
    {
        return $this->hasOne(Docente::class);
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class);
    }

    // Mutadores
    public function setNombresAttribute($value)
    {
        $this->attributes['nombres'] = strtoupper(trim($value));
    }

    public function setApellidosAttribute($value)
    {
        $this->attributes['apellidos'] = strtoupper(trim($value));
    }

    public function setCedulaAttribute($value)
    {
        $this->attributes['cedula'] = trim($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    public function getInicializAttribute()
    {
        $nombres = explode(' ', $this->nombres);
        $apellidos = explode(' ', $this->apellidos);

        $inicial = '';
        if (!empty($nombres[0]))
            $inicial .= substr($nombres[0], 0, 1);
        if (!empty($apellidos[0]))
            $inicial .= substr($apellidos[0], 0, 1);

        return strtoupper($inicial);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_usuario', $tipo);
    }

    public function scopeSearch($query, $term)
    {
        if (!$term)
            return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('nombres', 'LIKE', "%{$term}%")
                ->orWhere('apellidos', 'LIKE', "%{$term}%")
                ->orWhere('cedula', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%");
        });
    }

    // Métodos auxiliares
    public function esDocente()
    {
        return $this->tipo_usuario === 'docente' && $this->docente !== null;
    }

    public function esEstudiante()
    {
        return $this->tipo_usuario === 'estudiante' && $this->estudiante !== null;
    }

    public function esAdministrador()
    {
        return $this->tipo_usuario === 'administrador';
    }

    public function perfilCompleto()
    {
        $completo = !empty($this->nombres) && !empty($this->apellidos) && !empty($this->email);

        if ($this->esDocente()) {
            $completo = $completo && $this->docente !== null;
        } elseif ($this->esEstudiante()) {
            $completo = $completo && $this->estudiante !== null;
        }

        return $completo;
    }

    public function generarUsername()
    {
        $nombres = explode(' ', $this->nombres);
        $apellidos = explode(' ', $this->apellidos);

        $username = strtolower(
            ($nombres[0] ?? '') .
            ($apellidos[0] ?? '') .
            substr($this->cedula, -4)
        );

        return $username;
    }
}
