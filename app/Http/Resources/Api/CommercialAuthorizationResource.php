<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommercialAuthorizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'authorization_type' => $this->authorization_type,
            'authorization_label' => $this->authorization_type_label,
            'authorization_code' => $this->authorization_code,
            'description' => $this->description,
            'issuing_body' => $this->issuing_body,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'notes' => $this->notes,
        ];
    }
}
