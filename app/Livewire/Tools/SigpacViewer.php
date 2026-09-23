<?php

namespace App\Livewire\Tools;

use App\Services\Tools\SigpacParcelLookup;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class SigpacViewer extends Component
{
    /** Consultas por IP y minuto contra la API pública de SIGPAC. */
    public const MAX_LOOKUPS_PER_MINUTE = 20;

    public string $province = '';

    public string $municipality = '';

    public string $aggregate = '0';

    public string $zone = '0';

    public string $polygon = '';

    public string $parcel = '';

    public string $enclosure = '1';

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public ?string $status = null;

    /**
     * Si se pega la referencia completa en el primer campo (13:87:0:0:10:20:1),
     * se reparte entre los siete campos.
     */
    public function updatedProvince(string $value): void
    {
        $codes = app(SigpacParcelLookup::class)->parseReference($value);

        if ($codes !== null) {
            foreach ($codes as $field => $code) {
                $this->{$field} = (string) $code;
            }
        }
    }

    public function search(): void
    {
        $this->validate();
        $this->result = null;
        $this->status = null;

        $key = 'sigpac-viewer:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_LOOKUPS_PER_MINUTE)) {
            $this->addError('province', 'Demasiadas consultas seguidas. Espera un minuto.');

            return;
        }
        RateLimiter::hit($key, 60);

        $codes = [];
        foreach (SigpacParcelLookup::FIELDS as $field) {
            $codes[$field] = (int) $this->{$field};
        }

        $lookup = app(SigpacParcelLookup::class)->lookup($codes);
        $this->status = $lookup['status'];
        $this->result = $lookup['data'];

        if ($this->result && $this->result['wkt']) {
            $this->dispatch('sigpac-parcel-found', wkt: $this->result['wkt']);
        }
    }

    public function render()
    {
        return view('livewire.tools.sigpac-viewer');
    }

    protected function rules(): array
    {
        return [
            'province' => ['required', 'integer', 'between:1,99'],
            'municipality' => ['required', 'integer', 'between:1,999'],
            'aggregate' => ['required', 'integer', 'between:0,999'],
            'zone' => ['required', 'integer', 'between:0,999'],
            'polygon' => ['required', 'integer', 'between:1,99999'],
            'parcel' => ['required', 'integer', 'between:1,99999'],
            'enclosure' => ['required', 'integer', 'between:1,99999'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'province' => 'provincia',
            'municipality' => 'municipio',
            'aggregate' => 'agregado',
            'zone' => 'zona',
            'polygon' => 'polígono',
            'parcel' => 'parcela',
            'enclosure' => 'recinto',
        ];
    }
}
