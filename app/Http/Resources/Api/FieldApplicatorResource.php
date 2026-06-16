<?php

namespace App\Http\Resources\Api;

use App\Models\FieldApplicator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FieldApplicator */
class FieldApplicatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'name' => $this->name,
            'ropo_number' => $this->ropo_number,
            'ropo_category' => $this->ropo_category,
            'ropo_expiry_date' => $this->ropo_expiry_date?->toDateString(),
            'is_advisor' => (bool) $this->is_advisor,
            'advisor_license' => $this->advisor_license,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_ropo_expired' => $this->isRopoExpired(),
        ];
    }
}
