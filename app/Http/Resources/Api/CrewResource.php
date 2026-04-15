<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'members_count' => $this->whenCounted('members', $this->members_count ?? null),
            'activities_count' => $this->whenCounted('activities', $this->activities_count ?? null),
        ];
    }
}
