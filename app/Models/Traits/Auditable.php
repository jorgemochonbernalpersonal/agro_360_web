<?php

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Get audit logs for this model
     */
    /** @return MorphMany<AuditLog, $this> */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    /**
     * Get latest audit log
     */
    public function latestAudit(): ?AuditLog
    {
        return $this->auditLogs()->first();
    }

    /**
     * Get audit history
     */
    public function auditHistory(int $limit = 50): \Illuminate\Support\Collection
    {
        return $this->auditLogs()->limit($limit)->get();
    }

    /**
     * Boot the trait
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->auditCreation();
        });

        static::updated(function ($model) {
            $model->auditUpdate();
        });

        static::deleted(function ($model) {
            $model->auditDeletion();
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditRestoration();
            });
        }
    }

    /**
     * Audit creation
     */
    protected function auditCreation(): void
    {
        if (! $this->shouldAudit('created')) {
            return;
        }

        AuditLog::create([
            'user_id' => $this->getAuditUserId(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => 'created',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'new_values' => $this->getAuditableAttributes(),
            'metadata' => $this->getAuditMetadata(),
        ]);
    }

    /**
     * Audit update
     */
    protected function auditUpdate(): void
    {
        if (! $this->shouldAudit('updated')) {
            return;
        }

        // Respetar $auditExclude también en updates: si el modelo excluye
        // ciertos campos (p. ej. stock transaccional ya auditado en otra tabla),
        // no deben generar ruido aquí. Si tras excluir no queda nada, no auditamos.
        $excluded = $this->auditExclude ?? [];
        $changes = array_diff_key($this->getDirty(), array_flip($excluded));

        if (empty($changes)) {
            return;
        }

        AuditLog::create([
            'user_id' => $this->getAuditUserId(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => 'updated',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'old_values' => array_intersect_key($this->getOriginal(), $changes),
            'new_values' => $changes,
            'metadata' => $this->getAuditMetadata(),
        ]);
    }

    /**
     * Audit deletion
     */
    protected function auditDeletion(): void
    {
        if (! $this->shouldAudit('deleted')) {
            return;
        }

        AuditLog::create([
            'user_id' => $this->getAuditUserId(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => 'deleted',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'old_values' => $this->getAuditableAttributes(),
            'metadata' => $this->getAuditMetadata(),
        ]);
    }

    /**
     * Audit restoration
     */
    protected function auditRestoration(): void
    {
        if (! $this->shouldAudit('restored')) {
            return;
        }

        AuditLog::create([
            'user_id' => $this->getAuditUserId(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => 'restored',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'new_values' => $this->getAuditableAttributes(),
            'metadata' => $this->getAuditMetadata(),
        ]);
    }

    /**
     * Get current user ID for audit
     */
    protected function getAuditUserId(): ?int
    {
        return auth()->id();
    }

    /**
     * Get attributes to audit
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        // Excluir campos sensibles
        $excluded = $this->auditExclude ?? ['password', 'remember_token'];

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Get audit metadata
     */
    protected function getAuditMetadata(): array
    {
        return [
            'model' => class_basename($this),
            'route' => request()->route()?->getName(),
            'method' => request()->method(),
        ];
    }

    /**
     * Check if event should be audited
     */
    protected function shouldAudit(string $event): bool
    {
        // Si el modelo define eventos específicos a auditar
        if (property_exists($this, 'auditEvents')) {
            return in_array($event, $this->auditEvents);
        }

        // Por defecto, auditar todos los eventos
        return true;
    }
}
