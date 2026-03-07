<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerWasteType extends Model
{
    protected $fillable = ['name', 'description'];

    public function maintenanceWastes(): HasMany
    {
        return $this->hasMany(ContainerMaintenanceWaste::class);
    }
}
