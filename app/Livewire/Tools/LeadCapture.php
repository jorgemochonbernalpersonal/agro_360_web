<?php

namespace App\Livewire\Tools;

use App\Models\ToolLead;
use App\Notifications\NewToolLeadNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Formulario de captación de contactos embebido en las herramientas públicas.
 * Guarda el email con el contexto de la herramienta (p. ej. la puntuación del test).
 */
class LeadCapture extends Component
{
    public const MAX_ATTEMPTS_PER_HOUR = 5;

    #[Locked]
    public string $tool = '';

    /** @var array<string, mixed> */
    #[Locked]
    public array $context = [];

    #[Locked]
    public string $title = '¿Te ayudamos?';

    #[Locked]
    public string $subtitle = '';

    public string $email = '';

    public string $name = '';

    public string $profile = '';

    public bool $consent = false;

    /** Honeypot: los bots rellenan todos los campos. */
    public string $website = '';

    public bool $sent = false;

    public function save(): void
    {
        // Emails copiados/pegados suelen arrastrar espacios.
        $this->email = mb_strtolower(trim($this->email));
        $this->validate();

        if ($this->website !== '') {
            // Bot: fingimos éxito sin guardar nada.
            $this->sent = true;

            return;
        }

        $key = 'tool-lead:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS_PER_HOUR)) {
            $this->addError('email', 'Demasiados envíos seguidos. Inténtalo de nuevo más tarde.');

            return;
        }
        RateLimiter::hit($key, 3600);

        $lead = ToolLead::create([
            'email' => $this->email,
            'name' => trim($this->name) ?: null,
            'profile' => $this->profile ?: null,
            'tool' => $this->tool,
            'context' => $this->context ?: null,
            'ip_hash' => hash('sha256', (string) request()->ip().config('app.key')),
            'consented_at' => now(),
        ]);

        // El aviso interno no debe romper la experiencia del visitante.
        try {
            Notification::route('mail', config('support.email', config('mail.from.address', 'info@agro365.es')))
                ->notify(new NewToolLeadNotification($lead));
        } catch (\Throwable $e) {
            Log::error('Error al notificar nuevo contacto de herramientas: '.$e->getMessage(), ['lead_id' => $lead->id]);
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.tools.lead-capture', [
            'profiles' => ToolLead::PROFILES,
        ]);
    }

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:120'],
            'profile' => ['nullable', 'in:'.implode(',', array_keys(ToolLead::PROFILES))],
            'consent' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'consent.accepted' => 'Necesitamos tu consentimiento para poder escribirte.',
        ];
    }
}
