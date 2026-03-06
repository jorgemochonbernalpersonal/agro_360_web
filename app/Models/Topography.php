<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topography extends Model
{
    use HasFactory;

    protected $table = 'topographies';

    protected $fillable = ['name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
