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
}
