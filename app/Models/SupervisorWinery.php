<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SupervisorWinery extends Model
{
    protected $table = 'supervisor_winery';

    protected $fillable = [
        'supervisor_id',
        'winery_id',
        'assigned_by',
    ];

    protected static function booted(): void
    {
        static::created(function (SupervisorWinery $sw) {
            Cache::forget("winery:{$sw->winery_id}:has_supervisor");
        });

        static::deleting(function (SupervisorWinery $sw) {
            // Convert DO-assigned viticulturists to own — winery keeps them after DO unlinks
            WineryViticulturist::where('winery_id', $sw->winery_id)
                ->where('supervisor_id', $sw->supervisor_id)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->update([
                    'source'      => WineryViticulturist::SOURCE_OWN,
                    'supervisor_id' => null,
                ]);
        });

        static::deleted(function (SupervisorWinery $sw) {
            Cache::forget("winery:{$sw->winery_id}:has_supervisor");
            Cache::forget("winery:{$sw->winery_id}:pending_do_requests");
        });
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
