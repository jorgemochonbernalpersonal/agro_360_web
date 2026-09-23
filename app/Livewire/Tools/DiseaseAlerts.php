<?php

namespace App\Livewire\Tools;

use App\Models\DiseaseAlertSubscription;
use App\Models\Municipality;
use App\Services\Tools\DiseaseRiskForecast;
use App\Services\Tools\ToolLeadRecorder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
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

        $key = 'disease-alerts:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_SUBSCRIPTIONS_PER_HOUR)) {
            $this->addError('email', 'Demasiados envíos seguidos. Inténtalo de nuevo más tarde.');

            return;
        }
        RateLimiter::hit($key, 3600);

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
        ])->save();

        if ($isNew) {
            app(ToolLeadRecorder::class)->record($this->email, self::TOOL, [
                'municipality' => $municipality->name,
                'province' => $municipality->province?->name,
            ]);
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
