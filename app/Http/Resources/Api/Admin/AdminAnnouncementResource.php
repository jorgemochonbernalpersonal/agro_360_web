<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'is_active'  => (bool) $this->is_active,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'admin_id'   => $this->admin_id,
            'admin_name' => $this->whenLoaded('admin', fn () => $this->admin?->name),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
