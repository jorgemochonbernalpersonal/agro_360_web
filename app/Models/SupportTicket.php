<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'type',
        'status',
        'priority',
        'assigned_to',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Usuario que creó el ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario asignado al ticket
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Comentarios del ticket
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class, 'ticket_id');
    }

    /**
     * Scope para filtrar tickets abiertos
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope para filtrar tickets cerrados
     */
    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    /**
     * Scope para filtrar tickets de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por prioridad
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Marcar ticket como resuelto
     */
    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Cerrar ticket
     */
    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Reabrir ticket
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Verificar si el ticket está abierto
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Verificar si el ticket está cerrado
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Obtener badge color según prioridad
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtener badge color según estado
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtener label traducido del tipo
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'bug' => '🐛 Bug',
            'feature' => '✨ Nueva Funcionalidad',
            'improvement' => '🚀 Mejora',
            'question' => '❓ Pregunta',
            default => $this->type,
        };
    }

    /**
     * Obtener label traducido del estado
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado',
            default => $this->status,
        };
    }

    /**
     * Obtener label traducido de prioridad
     */
    public function getPriorityLabel(): string
    {
        return match ($this->priority) {
            'urgent' => '🔴 Urgente',
            'high' => '🟠 Alta',
            'medium' => '🟡 Media',
            'low' => '⚪ Baja',
            default => $this->priority,
        };
    }

    /**
     * Obtener URL de la imagen
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}
