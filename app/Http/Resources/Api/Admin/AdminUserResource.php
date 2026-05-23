<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'role'               => $this->role,
            'can_login'          => (bool) $this->can_login,
            'is_readonly_admin'  => (bool) $this->is_readonly_admin,
            'email_verified_at'  => $this->email_verified_at?->toIso8601String(),
            'last_login_at'      => $this->last_login_at?->toIso8601String(),
            'is_beta_user'       => (bool) $this->is_beta_user,
            'beta_access_granted'=> (bool) $this->beta_access_granted,
            'beta_ends_at'       => $this->beta_ends_at?->toIso8601String(),
            'compra_uva_externa' => (bool) $this->compra_uva_externa,
            'password_must_reset'=> (bool) $this->password_must_reset,
            'organization_id'    => $this->organization_id,
            'created_at'         => $this->created_at->toIso8601String(),
            'updated_at'         => $this->updated_at->toIso8601String(),
            'profile'            => $this->whenLoaded('profile', fn () => $this->profile ? [
                'phone'       => $this->profile->phone,
                'address'     => $this->profile->address,
                'city'        => $this->profile->city,
                'postal_code' => $this->profile->postal_code,
                'country'     => $this->profile->country,
                'province_id' => $this->profile->province_id,
            ] : null),
            'notes'              => $this->whenLoaded('adminNotes', fn () =>
                $this->adminNotes->map(fn ($note) => [
                    'id'         => $note->id,
                    'note'       => $note->note,
                    'admin_name' => $note->admin?->name,
                    'created_at' => $note->created_at?->toIso8601String(),
                ])->values()
            ),
        ];
    }
}
