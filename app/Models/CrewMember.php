<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewMember extends Model
{
    protected $table = 'crew_members';

    protected $fillable = [
        'crew_id',
        'viticulturist_id',
        'assigned_by',
        'phytosanitary_license_number',
        'license_expiry_date',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
    ];

    /**
     * Cuadrilla a la que pertenece (nullable para trabajadores individuales)
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class, 'crew_id');
    }

    /**
     * Verificar si es trabajador individual (sin cuadrilla)
     */
    public function isIndividual(): bool
    {
        return is_null($this->crew_id);
    }

    /**
     * Scope para trabajadores individuales de un viticultor
     */
    public function scopeIndividual($query, $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId)
                     ->whereNull('crew_id');
    }

    /**
     * Scope para trabajadores de un viticultor (individuales y en cuadrillas)
     */
    public function scopeForViticulturist($query, $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Viticultor miembro
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Usuario que asignó este miembro
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Verificar si el carnet fitosanitario está vigente
     */
    public function hasValidPhytosanitaryLicense(): bool
    {
        if (!$this->phytosanitary_license_number) {
            return false;
        }
        
        if (!$this->license_expiry_date) {
            return true; // Si no hay fecha de expiración, asumimos vigente
        }
        
        return \Carbon\Carbon::parse($this->license_expiry_date)->isFuture();
    }

    /**
     * Obtener estado del carnet (para mostrar en UI)
     */
    public function getLicenseStatusAttribute(): string
    {
        if (!$this->phytosanitary_license_number) {
            return 'No registrado';
        }
        
        if (!$this->license_expiry_date) {
            return 'Vigente';
        }
        
        $expiryDate = \Carbon\Carbon::parse($this->license_expiry_date);
        
        if ($expiryDate->isPast()) {
            return 'Caducado';
        }
        
        if ($expiryDate->diffInDays(now()) <= 30) {
            return 'Próximo a caducar';
        }
        
        return 'Vigente';
    }
}
