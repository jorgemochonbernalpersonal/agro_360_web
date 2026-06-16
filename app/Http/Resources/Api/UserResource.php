<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'has_winery' => (bool) $this->hasWinery(),
            'has_supervisor' => (bool) $this->hasSupervisor(),
            'password_must_reset' => (bool) $this->password_must_reset,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'beta_expired' => (bool) ($this->betaExpired() && ! $this->hasBasicFreeAccess()),
            'profile' => $this->whenLoaded('profile', fn () => $this->profile ? [
                'phone' => $this->profile->phone,
                'address' => $this->profile->address,
                'city' => $this->profile->city,
                'postal_code' => $this->profile->postal_code,
                'country' => $this->profile->country,
                'province_id' => $this->profile->province_id,
            ] : null),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
