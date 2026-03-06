<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name        = '';
    public string $year        = '';
    public string $start_date  = '';
    public string $end_date    = '';
    public string $description = '';

    public function mount(): void
    {
        $this->year       = (string) now()->year;
        $this->name       = "Vendimia {$this->year}";
        $this->start_date = now()->setMonth(8)->startOfMonth()->format('Y-m-d');
        $this->end_date   = now()->setMonth(11)->endOfMonth()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'year'        => ['required', 'integer', 'min:2000', 'max:2099'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'        => 'El nombre es obligatorio.',
            'year.required'        => 'El año es obligatorio.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior a la de inicio.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $wineryId = Auth::id();

        $exists = Campaign::forViticulturist($wineryId)
            ->forYear((int) $this->year)
            ->exists();

        if ($exists) {
            $this->addError('year', 'Ya existe una campaña para el año ' . $this->year . '.');
            return;
        }

        $campaign = Campaign::create([
            'name'           => $this->name,
            'year'           => (int) $this->year,
            'viticulturist_id' => $wineryId,
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'description'    => $this->description ?: null,
            'active'         => true,
        ]);

        // Desactivar otras campañas de la bodega
        Campaign::forViticulturist($wineryId)
            ->where('id', '!=', $campaign->id)
            ->update(['active' => false]);

        $this->toastSuccess("Campaña {$campaign->year} creada correctamente.");

        redirect()->route('winery.campaigns.index');
    }

    public function render()
    {
        return view('livewire.winery.harvest.campaigns.create')
            ->layout('layouts.app');
    }
}
