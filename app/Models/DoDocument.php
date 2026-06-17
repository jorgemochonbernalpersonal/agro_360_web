<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoDocument extends Model
{
    public const TYPE_PLIEGO = 'pliego';

    public const TYPE_REGLAMENTO = 'reglamento';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_LABELS = [
        'draft' => 'Borrador',
        'active' => 'Vigente',
        'archived' => 'Archivado',
    ];

    protected $table = 'do_documents';

    protected $fillable = [
        'supervisor_id',
        'type',
        'title',
        'version',
        'effective_date',
        'content',
        'status',
    ];

    protected $casts = [
        'effective_date' => 'date',
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

    public function scopeForSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
