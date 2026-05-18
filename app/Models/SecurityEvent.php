<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    // Los eventos son inmutables — solo created_at
    public $timestamps = false;

    protected $fillable = [
        'level',
        'event',
        'message',
        'ip',
        'user_agent',
        'email',
        'user_id',
        'admin_id',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    // Campos que no se duplican en context (ya tienen columna propia)
    public const CONTEXT_SKIP = [
        'event', 'ip', 'user_agent', 'email', 'user_email',
        'user_id', 'admin_id', 'timestamp',
    ];
}
