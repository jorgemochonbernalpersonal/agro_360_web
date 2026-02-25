<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidueAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'plot_planting_id',
        'viticulturist_id',
        'analysis_date',
        'sample_date',
        'laboratory_name',
        'laboratory_accreditation',
        'sample_type',
        'results',
        'overall_compliant',
        'certificate_file',
        'notes',
        'active',
    ];

    protected $casts = [
        'analysis_date'    => 'date',
        'sample_date'      => 'date',
        'results'          => 'array',
        'overall_compliant'=> 'boolean',
        'active'           => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function getComplianceColorAttribute(): string
    {
        return $this->overall_compliant ? 'green' : 'red';
    }
}
