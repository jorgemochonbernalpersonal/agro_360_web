<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'event' => $this->event,
            'message' => $this->message,
            'ip' => $this->ip,
            'email' => $this->email,
            'user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'context' => $this->context ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
