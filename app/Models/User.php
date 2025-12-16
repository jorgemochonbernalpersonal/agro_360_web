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
        'role',
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
     * Roles disponibles
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_WINERY = 'winery';
    public const ROLE_VITICULTURIST = 'viticulturist';

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Verificar si el usuario es supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    /**
     * Verificar si el usuario es winery
     */
    public function isWinery(): bool
    {
        return $this->role === self::ROLE_WINERY;
    }

    /**
     * Verificar si el usuario es viticulturist
     */
    public function isViticulturist(): bool
    {
        return $this->role === self::ROLE_VITICULTURIST;
    }

    // Relaciones como Supervisor
    public function supervisedWineries()
    {
        return $this->hasMany(SupervisorWinery::class, 'supervisor_id');
    }

    public function supervisedViticulturists()
    {
        return $this->hasMany(SupervisorViticulturist::class, 'supervisor_id');
    }

    // Relaciones como Winery
    public function supervisorRelations()
    {
        return $this->hasMany(SupervisorWinery::class, 'winery_id');
    }

    public function wineryViticulturists()
    {
        return $this->hasMany(WineryViticulturist::class, 'winery_id');
    }

    public function crews()
    {
        return $this->hasMany(Crew::class, 'winery_id');
    }

    // Relaciones como Viticulturist
    public function supervisorRelationsAsViticulturist()
    {
        return $this->hasMany(SupervisorViticulturist::class, 'viticulturist_id');
    }

    public function wineryRelationsAsViticulturist()
    {
        return $this->hasMany(WineryViticulturist::class, 'viticulturist_id');
    }

    public function parentHierarchies()
    {
        return $this->hasMany(ViticulturistHierarchy::class, 'parent_viticulturist_id');
    }

    public function childHierarchies()
    {
        return $this->hasMany(ViticulturistHierarchy::class, 'child_viticulturist_id');
    }

    public function ledCrews()
    {
        return $this->hasMany(Crew::class, 'viticulturist_id');
    }

    public function crewMemberships()
    {
        return $this->hasMany(CrewMember::class, 'viticulturist_id');
    }
}
