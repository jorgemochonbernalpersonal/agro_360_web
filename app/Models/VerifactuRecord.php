<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifactuRecord extends Model
{
    const STATUSES = [
        'pending'   => __('Pendiente'),
        'queued'    => __('En cola'),
        'submitted' => __('Enviada'),
        'accepted'  => __('Aceptada'),
        'rejected'  => __('Rechazada'),
        'cancelled' => __('Cancelada'),
    ];

    protected $fillable = [
        'user_id',
        'invoice_id',
        'submission_status',
        'submitted_at',
        'accepted_at',
        'rejected_at',
        'aeat_csv',
        'chain_hash',
        'qr_data',
        'response_code',
        'response_message',
        'error_details',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'accepted_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'error_details'=> 'array',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->submission_status] ?? $this->submission_status;
    }

    public function isAccepted(): bool
    {
        return $this->submission_status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->submission_status === 'rejected';
    }

    public function isPending(): bool
    {
        return in_array($this->submission_status, ['pending', 'queued']);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('submission_status', $status);
    }
}
