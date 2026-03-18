<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorRequest extends Model
{
    protected $table = 'supervisor_requests';

    protected $fillable = [
        'supervisor_id',
        'winery_id',
        'type',
        'status',
        'title',
        'notes',
        'response_notes',
        'sent_at',
        'responded_at',
        'resolved_at',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'responded_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    // ── Tipos ─────────────────────────────────────────────────────────────────

    public const TYPE_LABEL_REQUEST        = 'label_request';
    public const TYPE_QUALIFICATION        = 'qualification';
    public const TYPE_NONCONFORMITY        = 'nonconformity';
    public const TYPE_HARVEST_DECLARATION  = 'harvest_declaration';
    public const TYPE_CERTIFICATION        = 'certification';

    public const TYPE_LABELS = [
        'label_request'       => 'Solicitud de contraetiquetas',
        'qualification'       => 'Calificación de lote',
        'nonconformity'       => 'Acta de no conformidad',
        'harvest_declaration' => 'Declaración de cosecha',
        'certification'       => 'Certificación ecológica / IGP',
    ];

    /** Types initiated by the supervisor */
    public const SUPERVISOR_INITIATED = ['nonconformity'];

    /** Types initiated by the winery */
    public const WINERY_INITIATED = [
        'label_request', 'qualification', 'harvest_declaration', 'certification',
    ];

    // ── Estados ───────────────────────────────────────────────────────────────

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_PENDING    = 'pending';
    public const STATUS_IN_REVIEW  = 'in_review';
    public const STATUS_APPROVED   = 'approved';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_ARCHIVED   = 'archived';

    public const STATUS_LABELS = [
        'draft'     => 'Borrador',
        'pending'   => 'Pendiente',
        'in_review' => 'En revisión',
        'approved'  => 'Aprobada',
        'rejected'  => 'Rechazada',
        'archived'  => 'Archivada',
    ];

    public const STATUS_COLORS = [
        'draft'     => 'zinc',
        'pending'   => 'yellow',
        'in_review' => 'blue',
        'approved'  => 'green',
        'rejected'  => 'red',
        'archived'  => 'zinc',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeForWinery($query, int $wineryId)
    {
        return $query->where('winery_id', $wineryId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'zinc';
    }

    public function canBeSentBySupervisor(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeRespondedByWinery(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeResolvedBySupervisor(): bool
    {
        return $this->status === self::STATUS_IN_REVIEW;
    }

    // ── Transiciones ─────────────────────────────────────────────────────────

    public function send(): void
    {
        $this->update(['status' => self::STATUS_PENDING, 'sent_at' => now()]);
    }

    public function respond(string $responseNotes = ''): void
    {
        $this->update([
            'status'         => self::STATUS_IN_REVIEW,
            'response_notes' => $responseNotes ?: null,
            'responded_at'   => now(),
        ]);
    }

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED, 'resolved_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED, 'resolved_at' => now()]);
    }

    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }
}
