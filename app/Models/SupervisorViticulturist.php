<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorViticulturist extends Model
{
    protected $table = 'supervisor_viticulturist';

    protected $fillable = [
        'supervisor_id',
        'viticulturist_id',
        'assigned_by',
    ];

    /**
     * Supervisor que tiene este viticultor
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Viticultor asignado al supervisor
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Usuario que asignó esta relación
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
