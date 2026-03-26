<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'email'               => $this->email,
            'role'                => $this->role,
            'password_must_reset' => (bool) $this->password_must_reset,
            'email_verified_at'   => $this->email_verified_at?->toIso8601String(),
            'beta_expired'        => (bool) ($this->betaExpired() && !$this->hasBasicFreeAccess()),
            'profile'             => $this->whenLoaded('profile', fn () => [
                'phone'       => $this->profile->phone,
                'address'     => $this->profile->address,
                'city'        => $this->profile->city,
                'postal_code' => $this->profile->postal_code,
                'country'     => $this->profile->country,
                'province_id' => $this->profile->province_id,
            ]),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
