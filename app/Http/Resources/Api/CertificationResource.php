<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certification_type' => $this->certification_type,
            'certification_label' => $this->certification_type_label,
            'certifying_body' => $this->certifying_body,
            'certificate_number' => $this->certificate_number,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'scope' => $this->scope,
            'audit_date' => $this->audit_date?->toDateString(),
            'is_expired' => $this->is_expired,
            'is_expiring_soon' => $this->is_expiring_soon,
            'notes' => $this->notes,
        ];
    }
}
