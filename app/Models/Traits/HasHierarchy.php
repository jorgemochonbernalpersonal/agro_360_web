<?php

namespace App\Models\Traits;

use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasHierarchy
{
    /**
     * Cache properties (not stored in database)
     */
    protected $_wineries_cache;

    protected $_supervisor_cache;

    protected $_has_winery_cache;

    protected $_has_supervisor_cache;

    protected $_has_winery_supervisor_cache;

    protected $_was_created_by_another_cache;

    protected $_needs_password_change_cache;

    // ======== RELACIONES COMO SUPERVISOR ========

    /** @return HasMany<SupervisorWinery, $this> */
    public function supervisedWineries(): HasMany
    {
        return $this->hasMany(SupervisorWinery::class, 'supervisor_id');
    }

    /** @return HasMany<SupervisorViticulturist, $this> */
    public function supervisedViticulturists(): HasMany
    {
        return $this->hasMany(SupervisorViticulturist::class, 'supervisor_id');
    }

    // ======== RELACIONES COMO WINERY ========

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

    // ======== RELACIONES COMO VITICULTURIST ========

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

    public function individualWorkers()
    {
        return $this->hasMany(CrewMember::class, 'assigned_by')
            ->whereNull('crew_id');
    }

    // ======== MÉTODOS DE ACCESO A JERARQUÍA ========

    /**
     * Obtener el supervisor del viticultor
     * ✅ OPTIMIZADO: Usa Laravel Cache
     */
    public function getSupervisorAttribute(): ?User
    {
        if (! $this->hasViticulturistAccess()) {
            return null;
        }

        if (! isset($this->_supervisor_cache)) {
            $cacheKey = "user_{$this->id}_supervisor";

            $this->_supervisor_cache = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
                $relation = SupervisorViticulturist::where('viticulturist_id', $this->id)
                    ->with('supervisor')
                    ->first();

                return $relation?->supervisor;
            });
        }

        return $this->_supervisor_cache;
    }

    /**
     * Verificar si tiene supervisor.
     * Cached: in-memory + Laravel Cache 5 min.
     */
    public function hasSupervisor(): bool
    {
        if (! $this->hasViticulturistAccess()) {
            return false;
        }

        if (! isset($this->_has_supervisor_cache)) {
            $this->_has_supervisor_cache = \Illuminate\Support\Facades\Cache::remember(
                "user_{$this->id}_has_supervisor",
                300,
                fn () => SupervisorViticulturist::where('viticulturist_id', $this->id)->exists()
            );
        }

        return $this->_has_supervisor_cache;
    }

    /**
     * Obtener las wineries del viticultor
     * ✅ OPTIMIZADO: Usa Laravel Cache
     */
    public function getWineriesAttribute()
    {
        if (! $this->hasViticulturistAccess()) {
            return collect();
        }

        if (! isset($this->_wineries_cache)) {
            $cacheKey = "user_{$this->id}_wineries";

            $this->_wineries_cache = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
                $wineryIds = WineryViticulturist::where('viticulturist_id', $this->id)
                    ->whereNotNull('winery_id')
                    ->pluck('winery_id')
                    ->unique();

                if ($wineryIds->isEmpty()) {
                    return collect();
                }

                return User::whereIn('id', $wineryIds)
                    ->where('role', self::ROLE_WINERY)
                    ->select(['id', 'name', 'email', 'role'])
                    ->get();
            });
        }

        return $this->_wineries_cache;
    }

    /**
     * Verificar si tiene winery (vinculado con winery_id NOT NULL).
     * Cached: in-memory + Laravel Cache 5 min.
     */
    public function hasWinery(): bool
    {
        if (! $this->hasViticulturistAccess()) {
            return false;
        }

        if (! isset($this->_has_winery_cache)) {
            $this->_has_winery_cache = \Illuminate\Support\Facades\Cache::remember(
                "user_{$this->id}_has_winery",
                300,
                fn () => $this->wineryRelationsAsViticulturist()
                    ->whereNotNull('winery_id')
                    ->exists()
            );
        }

        return $this->_has_winery_cache;
    }

    /**
     * Verificar si esta bodega tiene supervisor (DO) vinculado.
     * Cached: in-memory + Laravel Cache 5 min.
     */
    public function hasWinerySupervisor(): bool
    {
        if (! $this->hasWineryAccess()) {
            return false;
        }

        if (! isset($this->_has_winery_supervisor_cache)) {
            $this->_has_winery_supervisor_cache = \Illuminate\Support\Facades\Cache::remember(
                "user_{$this->id}_has_winery_supervisor",
                300,
                fn () => SupervisorWinery::where('winery_id', $this->id)->exists()
            );
        }

        return $this->_has_winery_supervisor_cache;
    }

    /**
     * Verificar si puede editar un viticultor
     *
     * @param mixed $viticulturistId
     */
    public function canEditViticulturist($viticulturistId): bool
    {
        if (! $this->hasViticulturistAccess()) {
            return false;
        }

        return WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->exists();
    }

    /**
     * Verificar si el usuario puede seleccionar viticultores
     */
    public function canSelectViticulturist(): bool
    {
        if (in_array($this->role, ['admin', 'supervisor', 'winery', 'producer'])) {
            return true;
        }

        if ($this->hasViticulturistAccess()) {
            return WineryViticulturist::editableBy($this)->exists();
        }

        return false;
    }

    /**
     * Verificar si este viticultor fue creado por otro viticultor
     */
    public function wasCreatedByViticulturist(): bool
    {
        if (! $this->hasViticulturistAccess()) {
            return false;
        }

        return WineryViticulturist::where('viticulturist_id', $this->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->whereNotNull('parent_viticulturist_id')
            ->exists();
    }

    /**
     * Verificar si este usuario fue creado por otro usuario
     */
    public function wasCreatedByAnotherUser(): bool
    {
        if (! isset($this->_was_created_by_another_cache)) {
            $result = false;

            if ($this->hasViticulturistAccess()) {
                $result = WineryViticulturist::where('viticulturist_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->whereIn('source', [
                        WineryViticulturist::SOURCE_VITICULTURIST,
                        WineryViticulturist::SOURCE_OWN,
                        WineryViticulturist::SOURCE_SUPERVISOR,
                    ])
                    ->exists();
            } elseif ($this->isWinery()) {
                $result = SupervisorWinery::where('winery_id', $this->id)
                    ->whereNotNull('assigned_by')
                    ->exists();
            }

            $this->_was_created_by_another_cache = $result;
        }

        return $this->_was_created_by_another_cache;
    }

    /**
     * Verificar si el email está verificado y necesita cambiar contraseña
     */
    public function needsPasswordChange(): bool
    {
        if (! isset($this->_needs_password_change_cache)) {
            $this->_needs_password_change_cache = $this->wasCreatedByAnotherUser() && ! $this->hasVerifiedEmail();
        }

        return $this->_needs_password_change_cache;
    }

    /**
     * Limpiar cache de atributos calculados
     * ✅ OPTIMIZADO: También limpia cache de Laravel
     */
    public function clearAttributeCache(): void
    {
        unset($this->_wineries_cache);
        unset($this->_supervisor_cache);
        unset($this->_has_winery_cache);
        unset($this->_has_supervisor_cache);
        unset($this->_has_winery_supervisor_cache);
        unset($this->_was_created_by_another_cache);
        unset($this->_needs_password_change_cache);

        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_supervisor");
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_wineries");
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_has_winery");
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_has_supervisor");
        \Illuminate\Support\Facades\Cache::forget("user_{$this->id}_has_winery_supervisor");
    }
}
