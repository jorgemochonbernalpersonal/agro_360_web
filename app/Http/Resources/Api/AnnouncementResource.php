<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'body'         => $this->body,
            'type'         => $this->type,
            'winery_name'  => $this->winery?->name,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at'   => $this->expires_at?->toIso8601String(),
            'read_at'      => $this->pivot?->read_at,
        ];
    }
}
