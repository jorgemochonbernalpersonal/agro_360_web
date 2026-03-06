<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valley extends Model
{
    use HasFactory;

    protected $table = 'valleys';

    protected $fillable = ['code', 'name', 'description_valley', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function plots()
    {
        return $this->hasMany(Plot::class, 'valley_id');
    }
}
