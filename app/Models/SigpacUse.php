<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SigpacUse extends Model
{
    protected $table = 'sigpac_use';

    protected $fillable = [
        'code',
        'description',
    ];

    public function plots(): BelongsToMany
    {
        return $this->belongsToMany(Plot::class, 'plot_sigpac_use', 'sigpac_use_id', 'plot_id');
    }
}
