<?php

namespace App\Livewire\Viticulturist;

use App\Models\Tax;
use App\Models\UserTax;
use App\Models\InvoicingSetting;
use App\Models\DigitalSignature;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithToastNotifications;

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

    // === SIGNATURE TAB ===
    public $signaturePassword = '';
    public $signaturePassword_confirmation = '';
    public $hasDigitalSignature = false;
    
    // Para resetear contraseña de firma olvidada
    public $showResetPasswordModal = false;
    public $loginPasswordForReset = '';

    public function mount()
    {
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadDigitalSignature();
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
    }

    // ==========================================
    // TAXES TAB METHODS
    // ==========================================

    public function loadTaxes()
    {
        $user = Auth::user();
        
        // Obtener todos los impuestos disponibles
        $this->taxes = Tax::orderBy('rate', 'asc')->get();
        
        // Obtener el impuesto activo del usuario
        $userTax = UserTax::where('user_id', $user->id)->first();
        $this->activeTaxId = $userTax?->tax_id;
    }

    public function selectTax($taxId)
    {
        $user = Auth::user();
        
        // Eliminar impuesto anterior
        UserTax::where('user_id', $user->id)->delete();
        
        // Crear nuevo
        UserTax::create([
            'user_id' => $user->id,
            'tax_id' => $taxId,
            'is_default' => true,
            'order' => 1
        ]);
        
        $this->activeTaxId = $taxId;
        $this->toastSuccess('Impuesto configurado correctamente');
    }

    // ==========================================
    // INVOICING TAB METHODS
    // ==========================================

    public function loadInvoicing()
    {
        $user = Auth::user();
        
        // Obtener o crear configuración
        $settings = InvoicingSetting::forUser($user->id)->first();
        
        if (!$settings) {
            $settings = InvoicingSetting::createDefaultForUser($user->id);
        }

        // Cargar valores
        $this->invoice_prefix = $settings->invoice_prefix;
        $this->invoice_padding = $settings->invoice_padding;
        $this->invoice_counter = $settings->invoice_counter;
        $this->invoice_year_reset = $settings->invoice_year_reset;

        $this->delivery_note_prefix = $settings->delivery_note_prefix;
        $this->delivery_note_padding = $settings->delivery_note_padding;
        $this->delivery_note_counter = $settings->delivery_note_counter;
        $this->delivery_note_year_reset = $settings->delivery_note_year_reset;

        $this->updatePreviews();
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'invoice_') || str_starts_with($propertyName, 'delivery_note_')) {
            $this->updatePreviews();
        }
    }

    public function updatePreviews()
    {
        $this->invoicePreview = $this->replaceVariables($this->invoice_prefix) . 
                                str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);

        $this->deliveryNotePreview = $this->replaceVariables($this->delivery_note_prefix) . 
                                     str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
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

    public function saveInvoicing()
    {
        $this->validate([
            'invoice_prefix' => 'required|string|max:50',
            'invoice_padding' => 'required|integer|min:2|max:6',
            'invoice_counter' => 'required|integer|min:1',
            'delivery_note_prefix' => 'required|string|max:50',
            'delivery_note_padding' => 'required|integer|min:2|max:6',
            'delivery_note_counter' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $settings = InvoicingSetting::forUser($user->id)->first();

        $settings->update([
            'invoice_prefix' => $this->invoice_prefix,
            'invoice_padding' => $this->invoice_padding,
            'invoice_counter' => $this->invoice_counter,
            'invoice_year_reset' => $this->invoice_year_reset,
            'delivery_note_prefix' => $this->delivery_note_prefix,
            'delivery_note_padding' => $this->delivery_note_padding,
            'delivery_note_counter' => $this->delivery_note_counter,
            'delivery_note_year_reset' => $this->delivery_note_year_reset,
        ]);

        $this->updatePreviews();
        $this->toastSuccess('Configuración guardada exitosamente');
    }

    public function resetInvoiceCounter()
    {
        $this->invoice_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de facturas resetado. Haz clic en Guardar para aplicar.');
    }

    public function resetDeliveryNoteCounter()
    {
        $this->delivery_note_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de albaranes resetado. Haz clic en Guardar para aplicar.');
    }

    // ==========================================
    // SIGNATURE TAB METHODS
    // ==========================================

    public function loadDigitalSignature()
    {
        $user = Auth::user();
        $signature = DigitalSignature::forUser($user->id);
        $this->hasDigitalSignature = $signature !== null;
    }

    public function saveDigitalSignature()
    {
        // Validación inicial
        $this->validate([
            'signaturePassword' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // Validación de complejidad básica (puede mejorar con Package)
                'regex:/[a-z]/',      // al menos una minúscula
                'regex:/[A-Z]/',      // al menos una mayúscula
                'regex:/[0-9]/',      // al menos un número
            ],
        ], [
            'signaturePassword.required' => 'La contraseña de firma es obligatoria.',
            'signaturePassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'signaturePassword.confirmed' => 'Las contraseñas no coinciden.',
            'signaturePassword.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
        ]);
        
        // Lista de contraseñas prohibidas (comunes)
        $forbiddenPasswords = [
            'Password1', 'Password123', 'Password12345',
            'Agro3651', 'Agro365!', 'Agro365123',
            'Qwerty123', 'Abcd1234', 'Admin123',
            'Welcome1', 'Welcome123', 'Firma123',
            '12345678Aa', 'Aa123456', 'Password1!',
        ];
        
        // Verificar si la contraseña está en la lista prohibida (case-insensitive)
        foreach ($forbiddenPasswords as $forbidden) {
            if (strcasecmp($this->signaturePassword, $forbidden) === 0) {
                $this->addError('signaturePassword', 'Esta contraseña es demasiado común y predecible. Por seguridad, elige una contraseña más única para firmar documentos oficiales.');
                return;
            }
        }

        try {
            $user = Auth::user();
            $wasUpdate = $this->hasDigitalSignature;
            
            DigitalSignature::createOrUpdateForUser($user->id, $this->signaturePassword);
            
            $this->signaturePassword = '';
            $this->signaturePassword_confirmation = '';
            $this->hasDigitalSignature = true;
            
            // Emitir evento para que otros componentes se enteren
            $this->dispatch('signature-updated');
            
            $this->toastSuccess('Contraseña de firma digital ' . ($wasUpdate ? 'actualizada' : 'creada') . ' correctamente');
            
        } catch (\Exception $e) {
            $this->toastError('Error al guardar la contraseña de firma: ' . $e->getMessage());
        }
    }
    
    public function openResetPasswordModal()
    {
        $this->showResetPasswordModal = true;
    }
    
    public function closeResetPasswordModal()
    {
        $this->showResetPasswordModal = false;
        $this->loginPasswordForReset = '';
        $this->resetValidation('loginPasswordForReset');
    }
    
    public function resetForgottenSignaturePassword()
    {
        $this->validate([
            'loginPasswordForReset' => 'required|string',
        ], [
            'loginPasswordForReset.required' => 'Debes ingresar tu contraseña de login.',
        ]);
        
        // Verificar contraseña de login
        $user = Auth::user();
        if (!\Hash::check($this->loginPasswordForReset, $user->password)) {
            $this->addError('loginPasswordForReset', 'Contraseña de login incorrecta.');
            return;
        }
        
        // Resetear contraseña de firma (eliminarla)
        try {
            $signature = DigitalSignature::forUser($user->id);
            if ($signature) {
                $signature->delete();
                
                // Preparar datos del reseteo para el log y el email
                $resetData = [
                    'reset_at' => now()->format('d/m/Y H:i:s'),
                    'ip_address' => request()->ip(),
                    'browser' => $this->getBrowserName(request()->userAgent()),
                    'device' => $this->getDeviceName(request()->userAgent()),
                ];
                
                // Log del reseteo para auditoría
                \Log::warning('Signature password reset', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $resetData['ip_address'],
                    'browser' => $resetData['browser'],
                    'device' => $resetData['device'],
                ]);
                
                // Enviar email de notificación
                try {
                    \Mail::to($user->email)->send(
                        new \App\Mail\SignaturePasswordReset($user, $resetData)
                    );
                } catch (\Exception $emailError) {
                    // Log error pero no fallar operación
                    \Log::error('Error sending signature reset email: ' . $emailError->getMessage());
                }
            }
            
            $this->hasDigitalSignature = false;
            $this->closeResetPasswordModal();
            
            // Emitir evento
            $this->dispatch('signature-updated');
            
            $this->toastSuccess('Contraseña de firma eliminada. Te hemos enviado un email de confirmación. Ahora puedes crear una nueva.');
            
        } catch (\Exception $e) {
            $this->toastError('Error al resetear: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener nombre del navegador del user agent
     */
    protected function getBrowserName($userAgent)
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        return 'Desconocido';
    }
    
    /**
     * Obtener nombre del dispositivo del user agent
     */
    protected function getDeviceName($userAgent)
    {
        if (str_contains($userAgent, 'Mobile')) return 'Móvil';
        if (str_contains($userAgent, 'Tablet')) return 'Tablet';
        if (str_contains($userAgent, 'Windows')) return 'Windows PC';
        if (str_contains($userAgent, 'Macintosh')) return 'Mac';
        if (str_contains($userAgent, 'Linux')) return 'Linux PC';
        return 'Escritorio';
    }

    public function render()
    {
        return view('livewire.viticulturist.settings')->layout('layouts.app', [
            'title' => 'Configuración - Agro365',
            'description' => 'Gestiona la configuración de tu cuenta: impuestos, numeración de facturas y albaranes, y preferencias de facturación.',
        ]);
    }
}
