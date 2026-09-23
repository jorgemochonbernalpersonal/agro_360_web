<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Contacto captado desde las herramientas públicas gratuitas (/herramientas).
 *
 * @property string                    $email
 * @property string|null               $name
 * @property string|null               $profile
 * @property string                    $tool
 * @property array<string, mixed>|null $context
 * @property \Carbon\Carbon            $consented_at
 * @property \Carbon\Carbon|null       $contacted_at
 */
class ToolLead extends Model
{
    public const PROFILES = [
        'viticultor' => 'Viticultor / agricultor',
        'bodega' => 'Bodega',
        'cooperativa' => 'Cooperativa',
        'tecnico' => 'Técnico / asesor',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'email',
        'name',
        'profile',
        'tool',
        'context',
        'ip_hash',
        'consented_at',
        'contacted_at',
    ];

    protected $casts = [
        'context' => 'array',
        'consented_at' => 'datetime',
        'contacted_at' => 'datetime',
    ];

    /** @param Builder<ToolLead> $query */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('contacted_at');
    }

    public function profileLabel(): string
    {
        return self::PROFILES[$this->profile] ?? '—';
    }
}
