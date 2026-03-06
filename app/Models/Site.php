<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';

    protected $fillable = ['user_id', 'name', 'municipality_id', 'is_archived'];

    protected $casts = ['is_archived' => 'boolean'];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
