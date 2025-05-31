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
        'name',
        'email',
        'password',
        'rol_id',
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
     * Get the rol that owns the user.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function agricultor()
    {
        return $this->hasOne(Agricultor::class);
    }

    // Funciones auxiliares

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->rol && $this->rol->nombre === $roleName;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('ADMINISTRADOR');
    }

    /**
     * Verificar si el usuario puede acceder al panel agricultor
     */
    public function canAccessAgricultorPanel(): bool
    {
        return $this->isAdmin() || $this->hasRole('AGRICULTOR');
    }

    /**
     * Verificar si el usuario puede acceder al panel beneficio
     */
    public function canAccessBeneficioPanel(): bool
    {
        return $this->isAdmin() || $this->hasRole('BENEFICIO_CAFE');
    }

    /**
     * Verificar si el usuario puede acceder al panel peso cabal
     */
    public function canAccessPesoCabalPanel(): bool
    {
        return $this->isAdmin() || $this->hasRole('PESO_CABAL');
    }

    /**
     * Obtener el panel por defecto según el rol del usuario
     */
    public function getDefaultPanel(): string
    {
        if ($this->hasRole('AGRICULTOR')) {
            return 'agricultor';
        } elseif ($this->hasRole('BENEFICIO_CAFE')) {
            return 'beneficio';
        } elseif ($this->hasRole('PESO_CABAL')) {
            return 'pesoCabal';
        } elseif ($this->hasRole('ADMINISTRADOR')) {
            return 'beneficio'; // Panel por defecto para admin
        }

        return 'agricultor';
    }

    /**
     * Obtener la URL del panel por defecto según el rol del usuario
     */
    public function getDefaultPanelUrl(): string
    {
        return match ($this->getDefaultPanel()) {
            'agricultor' => '/',
            'beneficio' => '/beneficio',
            'pesoCabal' => '/peso-cabal',
            default => '/',
        };
    }
}
