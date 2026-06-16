<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoLabel extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobada',
        'issued' => 'Emitida',
        'cancelled' => 'Cancelada',
    ];

    public const STATUS_COLORS = [
        'pending' => 'yellow',
        'approved' => 'blue',
        'issued' => 'agro',
        'cancelled' => 'red',
    ];

    protected $table = 'do_labels';

    protected $fillable = [
        'supervisor_id',
        'winery_id',
        'supervisor_request_id',
        'vintage',
        'batch_number',
        'quantity_requested',
        'quantity_issued',
        'quantity_stock',
        'status',
        'notes',
        'requested_at',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'issued_at' => 'datetime',
        'vintage' => 'integer',
    ];

    public static function statusLabelOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUS_LABELS);
    }

    /** @return BelongsTo<User, $this> */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /** @return BelongsTo<User, $this> */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return BelongsTo<SupervisorRequest, $this> */
    public function supervisorRequest(): BelongsTo
    {
        return $this->belongsTo(SupervisorRequest::class, 'supervisor_request_id');
    }

    public function scopeForSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }
}
