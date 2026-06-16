<?php

namespace App\Http\Resources\Api;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_type' => $this->client_type,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->client_type === 'company'
                ? $this->company_document
                : $this->particular_document,
            'active' => (bool) $this->active,
            'balance' => $this->balance !== null ? (float) $this->balance : 0.0,
            'default_discount' => $this->default_discount !== null ? (float) $this->default_discount : 0.0,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
