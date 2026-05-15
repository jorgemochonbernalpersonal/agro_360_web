<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class NotebookAccessRequest extends Model
{
    protected $table = 'notebook_access_requests';

    protected static function booted(): void
    {
        $flush = fn (self $r) => Cache::forget("nav_badge_notebook_access_{$r->viticulturist_id}");

        static::saved(function (NotebookAccessRequest $request) use ($flush) {
            if ($request->wasRecentlyCreated || $request->wasChanged('status')) {
                $flush($request);
            }
        });

        static::deleted($flush);
    }

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'winery_id',
        'supervisor_id',
        'viticulturist_id',
        'status',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isFromSupervisor(): bool
    {
        return $this->supervisor_id !== null;
    }

    public function requester(): ?\App\Models\User
    {
        return $this->isFromSupervisor() ? $this->supervisor : $this->winery;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
