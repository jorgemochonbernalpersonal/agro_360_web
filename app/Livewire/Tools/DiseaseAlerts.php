<?php

namespace App\Livewire\Tools;

use App\Models\DiseaseAlertSubscription;
use App\Models\Municipality;
use App\Services\Tools\DiseaseRiskForecast;
use App\Services\Tools\ToolLeadRecorder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read Municipality|null $municipality
 */
class DiseaseAlerts extends Component
{
    public const TOOL = 'alertas-mildiu-oidio';

    public const MAX_SUBSCRIPTIONS_PER_HOUR = 5;

    public string $search = '';

    public ?int $municipalityId = null;

    public string $email = '';

    public bool $consent = false;

    /** Honeypot: los bots rellenan todos los campos. */
    public string $website = '';

    public bool $subscribed = false;

    public function selectMunicipality(int $id): void
    {
        $municipality = Municipality::whereNotNull('lat')->findOrFail($id);

        $this->municipalityId = $municipality->id;
        $this->search = $municipality->name;
        $this->subscribed = false;
        $this->resetValidation();
    }

    public function updatedSearch(): void
    {
        // Al volver a escribir se descarta el municipio elegido.
        $this->municipalityId = null;
    }

    /** @return Collection<int, Municipality> */
    #[Computed]
    public function suggestions(): Collection
    {
        if ($this->municipalityId || mb_strlen(trim($this->search)) < 3) {
            return collect();
        }

        return Municipality::with('province')
            ->whereNotNull('lat')
            ->where('name', 'like', '%'.trim($this->search).'%')
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [trim($this->search).'%'])
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function municipality(): ?Municipality
    {
        return $this->municipalityId ? Municipality::with('province')->find($this->municipalityId) : null;
    }

    /** @return array<int, array<string, mixed>>|null */
    #[Computed]
    public function forecast(): ?array
    {
        $municipality = $this->municipality;

        return $municipality
            ? app(DiseaseRiskForecast::class)->forLocation((float) $municipality->lat, (float) $municipality->lng)
            : null;
    }

    public function subscribe(): void
    {
        $this->email = mb_strtolower(trim($this->email));
        $this->validate();

        if ($this->website !== '') {
            $this->subscribed = true;

            return;
        }

        // Limitar por IP y también por email: limitar solo por IP es trivialmente evitable
        // rotando de IP, lo que permitiría spamear suscripciones (y, sin doble opt-in,
        // avisos) a la dirección de un tercero.
        $ipKey = 'disease-alerts:'.request()->ip();
        $emailKey = 'disease-alerts-email:'.$this->email;
        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_SUBSCRIPTIONS_PER_HOUR)
            || RateLimiter::tooManyAttempts($emailKey, self::MAX_SUBSCRIPTIONS_PER_HOUR)) {
            $this->addError('email', 'Demasiados envíos seguidos. Inténtalo de nuevo más tarde.');

            return;
        }
        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($emailKey, 3600);

        $municipality = $this->municipality;

        $subscription = DiseaseAlertSubscription::firstOrNew([
            'email' => $this->email,
            'municipality_id' => $municipality->id,
        ]);
        $isNew = ! $subscription->exists || $subscription->unsubscribed_at !== null;
        $subscription->fill([
            'lat' => $municipality->lat,
            'lng' => $municipality->lng,
            'unsubscribed_at' => null,
        ]);

        // Doble opt-in: mientras no esté confirmada, cada intento (re)genera el token y
        // reenvía el email de confirmación (por si el primero se perdió). No se activa
        // hasta que el destinatario confirme desde ese enlace — evita que cualquiera
        // pueda suscribir la dirección de un tercero sin su consentimiento. Si ya estaba
        // confirmada (p. ej. se da de alta otra vez tras haberse dado de baja), no hace
        // falta volver a confirmar.
        $needsConfirmation = ! $subscription->confirmed_at;
        if ($needsConfirmation) {
            $subscription->confirmation_token = Str::random(48);
        }

        $subscription->save();

        if ($isNew) {
            app(ToolLeadRecorder::class)->record($this->email, self::TOOL, [
                'municipality' => $municipality->name,
                'province' => $municipality->province?->name,
            ]);
        }

        if ($needsConfirmation) {
            Mail::send('emails.disease-alert-confirm', [
                'municipalityName' => $municipality->name,
                'confirmUrl' => route('tools.disease-alerts.confirm', $subscription->confirmation_token),
            ], function ($message) use ($subscription) {
                $message->to($subscription->email)
                    ->subject(__('Confirma tu suscripción a las alertas de Agro365'));
            });
        }

        $this->subscribed = true;
    }

    public function render()
    {
        return view('livewire.tools.disease-alerts');
    }

    protected function rules(): array
    {
        return [
            'municipalityId' => ['required', 'integer', 'exists:municipalities,id'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'municipalityId.required' => 'Elige primero tu municipio.',
            'consent.accepted' => 'Necesitamos tu consentimiento para enviarte las alertas.',
        ];
    }
}
