<?php

namespace App\Livewire\Producer;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\DigitalSignature;
use App\Models\InvoicingSetting;
use App\Models\Tax;
use App\Models\UserProfile;
use App\Models\UserTax;
use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use RuntimeException;

class Settings extends Component
{
    use WithToastNotifications;

    public $currentTab = 'taxes';

    protected $queryString = ['currentTab' => ['as' => 'tab']];

    // === TAXES TAB ===
    public $taxes;
    public $activeTaxId;

    // === INVOICING TAB ===
    public $invoice_prefix;
    public $invoice_padding;
    public $invoice_counter;
    public $invoice_year_reset;
    public $delivery_note_prefix;
    public $delivery_note_padding;
    public $delivery_note_counter;
    public $delivery_note_year_reset;
    public $invoicePreview;
    public $deliveryNotePreview;

    // === FISCAL TAB ===
    public $fiscal_nif          = '';
    public $fiscal_legal_name   = '';
    public $fiscal_address      = '';
    public $fiscal_city         = '';
    public $fiscal_postal_code  = '';
    public $fiscal_phone        = '';

    // === FIELDBOOK TAB ===
    public $default_limit_kg_per_ha = '';
    public $degree_day_base = 10;
    public $document_prefix_activity = 'ACT';
    public $document_prefix_harvest = 'VND';
    public $legal_text_fieldbook = '';
    public $notify_harvest_alerts = true;
    public $notify_activity_alerts = true;

    // === SIGNATURE TAB ===
    public $signaturePassword = '';
    public $signaturePassword_confirmation = '';
    public $hasDigitalSignature = false;

    public $showResetPasswordModal = false;
    public $loginPasswordForReset = '';

    public function mount(): void
    {
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadFiscal();
        $this->loadFieldbook();
        $this->loadDigitalSignature();
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
    }

    // ==========================================
    // TAXES
    // ==========================================

    public function loadTaxes(): void
    {
        $this->taxes     = Tax::orderBy('rate', 'asc')->get();
        $userTax         = UserTax::where('user_id', Auth::id())->first();
        $this->activeTaxId = $userTax?->tax_id;
    }

    public function selectTax($taxId): void
    {
        UserTax::where('user_id', Auth::id())->delete();
        UserTax::create([
            'user_id'    => Auth::id(),
            'tax_id'     => $taxId,
            'is_default' => true,
            'order'      => 1,
        ]);
        $this->activeTaxId = $taxId;
        $this->toastSuccess('Impuesto configurado correctamente');
    }

    // ==========================================
    // INVOICING
    // ==========================================

    public function loadInvoicing(): void
    {
        $settings = InvoicingSetting::forUser(Auth::id())->first()
            ?? InvoicingSetting::createDefaultForUser(Auth::id());

        $this->invoice_prefix           = $settings->invoice_prefix;
        $this->invoice_padding          = $settings->invoice_padding;
        $this->invoice_counter          = $settings->invoice_counter;
        $this->invoice_year_reset       = $settings->invoice_year_reset;
        $this->delivery_note_prefix     = $settings->delivery_note_prefix;
        $this->delivery_note_padding    = $settings->delivery_note_padding;
        $this->delivery_note_counter    = $settings->delivery_note_counter;
        $this->delivery_note_year_reset = $settings->delivery_note_year_reset;

        $this->updatePreviews();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'invoice_') || str_starts_with($propertyName, 'delivery_note_')) {
            $this->updatePreviews();
        }
    }

    public function updatePreviews(): void
    {
        $this->invoicePreview      = $this->replaceVariables($this->invoice_prefix)
            . str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);
        $this->deliveryNotePreview = $this->replaceVariables($this->delivery_note_prefix)
            . str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
    }

    protected function replaceVariables(string $prefix): string
    {
        $now = now();
        return str_replace(
            ['{YEAR}', '{MONTH}', '{DAY}'],
            [$now->format('Y'), $now->format('m'), $now->format('d')],
            $prefix
        );
    }

    public function saveInvoicing(): void
    {
        $this->validate([
            'invoice_prefix'        => 'required|string|max:50',
            'invoice_padding'       => 'required|integer|min:2|max:6',
            'invoice_counter'       => 'required|integer|min:1',
            'delivery_note_prefix'  => 'required|string|max:50',
            'delivery_note_padding' => 'required|integer|min:2|max:6',
            'delivery_note_counter' => 'required|integer|min:1',
        ]);

        $settings = InvoicingSetting::forUser(Auth::id())->first();
        $settings->update([
            'invoice_prefix'           => $this->invoice_prefix,
            'invoice_padding'          => $this->invoice_padding,
            'invoice_counter'          => $this->invoice_counter,
            'invoice_year_reset'       => $this->invoice_year_reset,
            'delivery_note_prefix'     => $this->delivery_note_prefix,
            'delivery_note_padding'    => $this->delivery_note_padding,
            'delivery_note_counter'    => $this->delivery_note_counter,
            'delivery_note_year_reset' => $this->delivery_note_year_reset,
        ]);

        $this->updatePreviews();
        $this->toastSuccess('Configuración guardada correctamente');
    }

    public function resetInvoiceCounter(): void
    {
        $this->invoice_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de facturas reseteado. Haz clic en Guardar para aplicar.');
    }

    public function resetDeliveryNoteCounter(): void
    {
        $this->delivery_note_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de albaranes reseteado. Haz clic en Guardar para aplicar.');
    }

    // ==========================================
    // FISCAL
    // ==========================================

    public function loadFiscal(): void
    {
        $user    = Auth::user();
        $profile = UserProfile::where('user_id', $user->id)->first();
        $inv     = InvoicingSetting::forUser($user->id)->first();

        $this->fiscal_nif         = $user->dni ?? '';
        $this->fiscal_legal_name  = $inv?->issuer_legal_name ?? '';
        $this->fiscal_address     = $profile?->address ?? '';
        $this->fiscal_city        = $profile?->city ?? '';
        $this->fiscal_postal_code = $profile?->postal_code ?? '';
        $this->fiscal_phone       = $profile?->phone ?? '';
    }

    public function saveFiscal(): void
    {
        $this->validate([
            'fiscal_nif'         => 'nullable|string|max:20',
            'fiscal_legal_name'  => 'nullable|string|max:150',
            'fiscal_address'     => 'nullable|string|max:255',
            'fiscal_city'        => 'nullable|string|max:100',
            'fiscal_postal_code' => 'nullable|string|max:10',
            'fiscal_phone'       => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update(['dni' => $this->fiscal_nif ?: null]);

        $inv = InvoicingSetting::forUser($user->id)->first()
            ?? InvoicingSetting::createDefaultForUser($user->id);
        $inv->update(['issuer_legal_name' => $this->fiscal_legal_name ?: null]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address'     => $this->fiscal_address ?: null,
                'city'        => $this->fiscal_city ?: null,
                'postal_code' => $this->fiscal_postal_code ?: null,
                'phone'       => $this->fiscal_phone ?: null,
            ]
        );

        $this->toastSuccess('Datos fiscales guardados correctamente');
    }

    // ==========================================
    // FIELDBOOK
    // ==========================================

    public function loadFieldbook(): void
    {
        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $this->default_limit_kg_per_ha  = $settings->default_limit_kg_per_ha ?? '';
        $this->degree_day_base          = $settings->degree_day_base;
        $this->document_prefix_activity = $settings->document_prefix_activity;
        $this->document_prefix_harvest  = $settings->document_prefix_harvest;
        $this->legal_text_fieldbook     = $settings->legal_text_fieldbook ?? '';
        $this->notify_harvest_alerts    = $settings->notify_harvest_alerts;
        $this->notify_activity_alerts   = $settings->notify_activity_alerts;
    }

    public function saveFieldbook(): void
    {
        $this->validate([
            'default_limit_kg_per_ha'  => 'nullable|numeric|min:0|max:999999',
            'degree_day_base'          => 'required|numeric|min:0|max:30',
            'document_prefix_activity' => 'required|string|max:20|regex:/^[A-Z0-9_\-]+$/',
            'document_prefix_harvest'  => 'required|string|max:20|regex:/^[A-Z0-9_\-]+$/',
            'legal_text_fieldbook'     => 'nullable|string|max:2000',
        ], [
            'document_prefix_activity.regex' => 'Solo letras mayúsculas, números, guión y guión bajo.',
            'document_prefix_harvest.regex'  => 'Solo letras mayúsculas, números, guión y guión bajo.',
        ]);

        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $settings->update([
            'default_limit_kg_per_ha'  => $this->default_limit_kg_per_ha ?: null,
            'degree_day_base'          => $this->degree_day_base,
            'document_prefix_activity' => strtoupper($this->document_prefix_activity),
            'document_prefix_harvest'  => strtoupper($this->document_prefix_harvest),
            'legal_text_fieldbook'     => $this->legal_text_fieldbook ?: null,
            'notify_harvest_alerts'    => $this->notify_harvest_alerts,
            'notify_activity_alerts'   => $this->notify_activity_alerts,
        ]);

        $this->loadFieldbook();
        $this->toastSuccess('Configuración del cuaderno guardada correctamente');
    }

    // ==========================================
    // DIGITAL SIGNATURE
    // ==========================================

    public function loadDigitalSignature(): void
    {
        $this->hasDigitalSignature = DigitalSignature::forUser(Auth::id()) !== null;
    }

    public function saveDigitalSignature(): void
    {
        $this->validate([
            'signaturePassword' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'signaturePassword.required'  => 'La contraseña de firma es obligatoria.',
            'signaturePassword.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'signaturePassword.confirmed' => 'Las contraseñas no coinciden.',
            'signaturePassword.regex'     => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
        ]);

        $forbiddenPasswords = [
            'Password1', 'Password123', 'Password12345',
            'Agro3651', 'Agro365!', 'Agro365123',
            'Qwerty123', 'Abcd1234', 'Admin123',
            'Welcome1', 'Welcome123', 'Firma123',
            '12345678Aa', 'Aa123456', 'Password1!',
        ];

        foreach ($forbiddenPasswords as $forbidden) {
            if (strcasecmp($this->signaturePassword, $forbidden) === 0) {
                $this->addError('signaturePassword', 'Esta contraseña es demasiado común y predecible. Por seguridad, elige una contraseña más única para firmar documentos oficiales.');
                return;
            }
        }

        try {
            $wasUpdate = $this->hasDigitalSignature;
            DigitalSignature::createOrUpdateForUser(Auth::id(), $this->signaturePassword);

            $this->signaturePassword              = '';
            $this->signaturePassword_confirmation = '';
            $this->hasDigitalSignature            = true;

            $this->dispatch('signature-updated');
            $this->toastSuccess('Contraseña de firma digital ' . ($wasUpdate ? 'actualizada' : 'creada') . ' correctamente');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : 'Error al guardar la configuración. Inténtalo de nuevo.');
        }
    }

    public function openResetPasswordModal(): void
    {
        $this->showResetPasswordModal = true;
    }

    public function closeResetPasswordModal(): void
    {
        $this->showResetPasswordModal = false;
        $this->loginPasswordForReset  = '';
        $this->resetValidation('loginPasswordForReset');
    }

    public function resetForgottenSignaturePassword(): void
    {
        $this->validate([
            'loginPasswordForReset' => 'required|string',
        ], [
            'loginPasswordForReset.required' => 'Debes ingresar tu contraseña de login.',
        ]);

        $user = Auth::user();
        if (!\Hash::check($this->loginPasswordForReset, $user->password)) {
            $this->addError('loginPasswordForReset', 'Contraseña de login incorrecta.');
            return;
        }

        try {
            $signature = DigitalSignature::forUser($user->id);
            if ($signature) {
                $resetData = [
                    'reset_at'   => now()->format('d/m/Y H:i:s'),
                    'ip_address' => request()->ip(),
                    'browser'    => $this->getBrowserName(request()->userAgent()),
                    'device'     => $this->getDeviceName(request()->userAgent()),
                ];

                \Log::warning('Signature password reset', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'ip'      => $resetData['ip_address'],
                    'browser' => $resetData['browser'],
                    'device'  => $resetData['device'],
                ]);

                try {
                    \Mail::to($user->email)->send(
                        new \App\Mail\SignaturePasswordReset($user, $resetData)
                    );
                } catch (\Exception $emailError) {
                    \Log::error('Error sending signature reset email: ' . $emailError->getMessage());
                }

                $signature->delete();
            }

            $this->hasDigitalSignature = false;
            $this->closeResetPasswordModal();
            $this->dispatch('signature-updated');
            $this->toastSuccess('Contraseña de firma eliminada. Te hemos enviado un email de confirmación. Ahora puedes crear una nueva.');
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : 'Error al resetear. Inténtalo de nuevo.');
        }
    }

    protected function getBrowserName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome'))  return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari'))  return 'Safari';
        if (str_contains($userAgent, 'Edge'))    return 'Edge';
        if (str_contains($userAgent, 'Opera'))   return 'Opera';
        return 'Desconocido';
    }

    protected function getDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile'))    return 'Móvil';
        if (str_contains($userAgent, 'Tablet'))    return 'Tablet';
        if (str_contains($userAgent, 'Windows'))   return 'Windows PC';
        if (str_contains($userAgent, 'Macintosh')) return 'Mac';
        if (str_contains($userAgent, 'Linux'))     return 'Linux PC';
        return 'Escritorio';
    }

    public function render()
    {
        return view('livewire.producer.settings')->layout('layouts.app', [
            'title'       => 'Configuración - Agro365',
            'description' => 'Gestiona la configuración de tu cuenta: impuestos, numeración de facturas, datos fiscales y cuaderno de campo.',
        ]);
    }
}
