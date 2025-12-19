<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'password_must_reset',
        'can_login',
    ];

    /**
     * Cache properties (not stored in database)
     */
    protected $_wineries_cache;
    protected $_supervisor_cache;
    protected $_was_created_by_another_cache;
    protected $_needs_password_change_cache;

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
            'password_must_reset' => 'boolean',
            'can_login' => 'boolean',
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

    public function ledCrews()
    {
        return $this->hasMany(Crew::class, 'viticulturist_id');
    }

    public function crewMemberships()
    {
        return $this->hasMany(CrewMember::class, 'viticulturist_id');
    }

    // Relaciones de perfil y suscripciones
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verificar si el usuario tiene una suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Trabajadores individuales gestionados por este viticultor
     */
    public function individualWorkers()
    {
        return $this->hasMany(CrewMember::class, 'assigned_by')
                    ->whereNull('crew_id');
    }

    /**
     * Obtener el supervisor del viticultor (desde WineryViticulturist)
     * Busca en WineryViticulturist con source = 'supervisor'
     * Cacheado para evitar queries repetidas
     */
    public function getSupervisorAttribute(): ?User
    {
        if (!$this->isViticulturist()) {
            return null;
        }
        
        // Cachear en memoria durante la request
        if (!isset($this->_supervisor_cache)) {
            $wineryRelation = WineryViticulturist::where('viticulturist_id', $this->id)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->whereNotNull('supervisor_id')
                ->with('supervisor')
                ->first();
            
            $this->_supervisor_cache = $wineryRelation?->supervisor;
        }
        
        return $this->_supervisor_cache;
    }

    /**
     * Verificar si tiene supervisor
     */
    public function hasSupervisor(): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereNotNull('supervisor_id')
            ->exists();
    }

    /**
     * Obtener las wineries del viticultor (usando relación existente)
     * Cacheado para evitar queries repetidas
     */
    public function getWineriesAttribute()
    {
        if (!$this->isViticulturist()) {
            return collect();
        }
        
        // Cachear en memoria durante la request
        if (!isset($this->_wineries_cache)) {
            $this->_wineries_cache = $this->wineryRelationsAsViticulturist()
                ->with('winery')
                ->get()
                ->pluck('winery')
                ->filter()
                ->unique('id')
                ->values();
        }
        
        return $this->_wineries_cache;
    }

    /**
     * Verificar si tiene winery
     */
    public function hasWinery(): bool
    {
        return $this->isViticulturist() && $this->wineryRelationsAsViticulturist()->exists();
    }

    /**
     * Verificar si puede editar un viticultor (solo los que creó)
     */
    public function canEditViticulturist($viticulturistId): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->exists();
    }

    /**
     * Verificar si el usuario puede seleccionar viticultores
     * Admin, supervisor y winery siempre pueden seleccionar
     * Viticultores solo pueden seleccionar si tienen viticultores creados
     */
    public function canSelectViticulturist(): bool
    {
        // Admin, supervisor y winery siempre pueden seleccionar
        if (in_array($this->role, ['admin', 'supervisor', 'winery'])) {
            return true;
        }
        
        // Viticultores solo pueden seleccionar si tienen viticultores creados
        if ($this->isViticulturist()) {
            return WineryViticulturist::editableBy($this)->exists();
        }
        
        return false;
    }

    /**
     * Verificar si este viticultor fue creado por otro viticultor
     */
    public function wasCreatedByViticulturist(): bool
    {
        if (!$this->isViticulturist()) {
            return false;
        }
        
        return WineryViticulturist::where('viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->whereNotNull('parent_viticulturist_id')
            ->exists();
    }

    /**
     * Verificar si este usuario fue creado por otro usuario (viticultor, winery o supervisor)
     * Esto indica que tiene una contraseña temporal y debe cambiarla
     * Cacheado para evitar queries repetidas en cada request
     */
    public function wasCreatedByAnotherUser(): bool
    {
        // Cachear en memoria durante la request
        if (!isset($this->_was_created_by_another_cache)) {
            $result = false;
            
            // Si es viticultor, verificar si fue creado por otro usuario
            if ($this->isViticulturist()) {
                $result = WineryViticulturist::where('viticulturist_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->whereIn('source', [
                        WineryViticulturist::SOURCE_VITICULTURIST,
                        WineryViticulturist::SOURCE_OWN,
                        WineryViticulturist::SOURCE_SUPERVISOR
                    ])
                    ->exists();
            }
            // Si es winery, verificar si fue creado por supervisor
            elseif ($this->isWinery()) {
                $result = \App\Models\SupervisorWinery::where('winery_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->exists();
            }
            
            $this->_was_created_by_another_cache = $result;
        }
        
        return $this->_was_created_by_another_cache;
    }

    /**
     * Verificar si el email está verificado
     * Si fue creado por otro usuario y no ha verificado email, necesita cambiar contraseña
     * Cacheado para evitar queries repetidas en cada request
     */
    public function needsPasswordChange(): bool
    {
        // Cachear en memoria durante la request
        if (!isset($this->_needs_password_change_cache)) {
            $this->_needs_password_change_cache = $this->wasCreatedByAnotherUser() && !$this->hasVerifiedEmail();
        }
        
        return $this->_needs_password_change_cache;
    }

    /**
     * Limpiar cache de atributos calculados
     * Útil cuando se actualiza el usuario (verificación de email, cambio de contraseña, etc.)
     */
    public function clearAttributeCache(): void
    {
        unset($this->_wineries_cache);
        unset($this->_supervisor_cache);
        unset($this->_was_created_by_another_cache);
        unset($this->_needs_password_change_cache);
    }

    /**
     * Boot del modelo - limpiar cache cuando se actualiza
     */
    protected static function booted()
    {
        // Limpiar propiedades de cache antes de guardar para evitar que se guarden en la BD
        static::saving(function ($user) {
            unset($user->_wineries_cache);
            unset($user->_supervisor_cache);
            unset($user->_was_created_by_another_cache);
            unset($user->_needs_password_change_cache);
        });

        static::saved(function ($user) {
            $user->clearAttributeCache();
            // Limpiar cache de sesión cuando se actualiza el usuario
            if ($user->wasChanged(['email_verified_at', 'password'])) {
                session()->forget("user_{$user->id}_needs_password_change");
            }
        });
    }
}
