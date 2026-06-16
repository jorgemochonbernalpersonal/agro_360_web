<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignDocument extends Model
{
    use HasFactory;

    const DOCUMENT_TYPES = [
        'invoice' => 'Factura de insumos',
        'certificate' => 'Certificado (orgánico, DO, etc.)',
        'lab_report' => 'Informe de laboratorio',
        'authorization' => 'Autorización',
        'map' => 'Mapa / croquis',
        'analysis' => 'Análisis (suelo, agua...)',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'campaign_id',
        'viticulturist_id',
        'name',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_kb',
        'notes',
    ];

    protected $casts = [
        'file_size_kb' => 'integer',
    ];

    public static function documentTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::DOCUMENT_TYPES);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return __(self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size_kb) {
            return '-';
        }
        if ($this->file_size_kb >= 1024) {
            return round($this->file_size_kb / 1024, 1).' MB';
        }

        return $this->file_size_kb.' KB';
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }
}
