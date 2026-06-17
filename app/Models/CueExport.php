<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CueExport extends Model
{
    use HasFactory;

    const STATUSES = [
        'draft' => 'Borrador',
        'generated' => 'Generado',
        'sent' => 'Enviado',
        'accepted' => 'Aceptado',
        'rejected' => 'Rechazado',
    ];

    const STATUS_COLORS = [
        'draft' => 'zinc',
        'generated' => 'blue',
        'sent' => 'amber',
        'accepted' => 'green',
        'rejected' => 'red',
    ];

    protected $fillable = [
        'exploitation_id',
        'viticulturist_id',
        'campaign_year',
        'period_type',
        'from_date',
        'to_date',
        'status',
        'payload_json',
        'response_json',
        'generated_at',
        'sent_at',
        'accepted_at',
        'error_message',
        'file_path',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'campaign_year' => 'integer',
        'payload_json' => 'array',
        'response_json' => 'array',
        'generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    /** @return BelongsTo<Exploitation, $this> */
    public function exploitation(): BelongsTo
    {
        return $this->belongsTo(Exploitation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status];
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status];
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->whereHas('exploitation', fn ($q) => $q->where('viticulturist_id', $viticulturistId));
    }
}
