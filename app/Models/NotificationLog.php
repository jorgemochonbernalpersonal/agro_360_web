<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'admin_id',
        'subject',
        'message',
        'audience_role',
        'audience_verified',
        'audience_active',
        'recipient_count',
    ];

    protected $casts = [
        'audience_verified' => 'boolean',
        'audience_active' => 'boolean',
    ];

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function audienceLabel(): string
    {
        $roleMap = [
            'all' => 'Todos',
            'viticulturist' => 'Viticultores',
            'winery' => 'Bodegas',
            'supervisor' => 'Supervisores',
            'producer' => 'Productores',
            'admin' => 'Admins',
        ];

        return $roleMap[$this->audience_role] ?? $this->audience_role;
    }
}
