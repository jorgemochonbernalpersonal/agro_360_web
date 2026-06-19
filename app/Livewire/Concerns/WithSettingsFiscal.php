<?php

namespace App\Livewire\Concerns;

use App\Models\InvoicingSetting;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;

trait WithSettingsFiscal
{
    public string $fiscal_nif = '';

    public string $fiscal_legal_name = '';

    public string $fiscal_address = '';

    public string $fiscal_city = '';

    public string $fiscal_postal_code = '';

    public string $fiscal_phone = '';

    public function loadFiscal(): void
    {
        $user = Auth::user();
        $profile = UserProfile::where('user_id', $user->id)->first();
        $inv = InvoicingSetting::forUser($user->id)->first();

        $this->fiscal_nif = $user->dni ?? '';
        $this->fiscal_legal_name = $inv->issuer_legal_name ?? '';
        $this->fiscal_address = $profile->address ?? '';
        $this->fiscal_city = $profile->city ?? '';
        $this->fiscal_postal_code = $profile->postal_code ?? '';
        $this->fiscal_phone = $profile->phone ?? '';
    }

    public function saveFiscal(): void
    {
        $this->validate([
            'fiscal_nif' => 'nullable|string|max:20',
            'fiscal_legal_name' => 'nullable|string|max:150',
            'fiscal_address' => 'nullable|string|max:255',
            'fiscal_city' => 'nullable|string|max:100',
            'fiscal_postal_code' => 'nullable|string|max:10',
            'fiscal_phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update(['dni' => $this->fiscal_nif ?: null]);

        $inv = InvoicingSetting::forUser($user->id)->first()
            ?? InvoicingSetting::createDefaultForUser($user->id);
        $inv->update(['issuer_legal_name' => $this->fiscal_legal_name ?: null]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $this->fiscal_address ?: null,
                'city' => $this->fiscal_city ?: null,
                'postal_code' => $this->fiscal_postal_code ?: null,
                'phone' => $this->fiscal_phone ?: null,
            ]
        );

        $this->toastSuccess(__('Datos fiscales guardados correctamente'));
    }
}
