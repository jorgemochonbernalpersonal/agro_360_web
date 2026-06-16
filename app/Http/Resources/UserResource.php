<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'profile' => $this->whenLoaded('profile', fn () => [
                'nif_cif' => $this->profile->nif_cif,
                'phone' => $this->profile->phone,
                'address' => $this->profile->address,
                'city' => $this->profile->city,
                'postal_code' => $this->profile->postal_code,
                'province_id' => $this->profile->province_id,
            ]),
            'subscription' => $this->when(
                $this->relationLoaded('activeSubscription') && $this->activeSubscription,
                fn () => new SubscriptionResource($this->activeSubscription)
            ),
            'beta_access' => [
                'is_beta_user' => $this->is_beta_user,
                'beta_ends_at' => $this->beta_ends_at?->toIso8601String(),
                'days_remaining' => $this->betaDaysRemaining(),
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
