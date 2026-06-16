<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSystem extends Model
{
    use HasFactory;

    protected $table = 'training_systems';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Plantaciones que usan este sistema de conducción.
     */
    /** @return HasMany<PlotPlanting, $this> */
    public function plantings(): HasMany
    {
        return $this->hasMany(PlotPlanting::class, 'training_system_id');
    }
}
