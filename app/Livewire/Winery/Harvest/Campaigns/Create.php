<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Campaign;
use Illuminate\Validation\ValidationException;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $description = '';

    public function mount(): void
    {
        $this->year = (string) now()->year;
        $this->name = "Vendimia {$this->year}";
        $this->start_date = now()->setMonth(8)->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->setMonth(11)->endOfMonth()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function performCreate(): void
    {
        $wineryId = $this->ownerId();

        if (Campaign::forViticulturist($wineryId)->forYear((int) $this->year)->exists()) {
            throw ValidationException::withMessages([
                'year' => __('Ya existe una campaña para el año :year.', ['year' => $this->year]),
            ]);
        }

        $campaign = Campaign::create([
            'name' => $this->name,
            'year' => (int) $this->year,
            'viticulturist_id' => $wineryId,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'description' => $this->description ?: null,
            'active' => true,
        ]);

        Campaign::forViticulturist($wineryId)
            ->where('id', '!=', $campaign->id)
            ->update(['active' => false]);
    }

    protected function ownerColumn(): string
    {
        return 'viticulturist_id';
    }

    protected function successMessage(): string
    {
        return "Campaña {$this->year} creada correctamente.";
    }

    protected function indexRoute(): string
    {
        return 'winery.campaigns.index';
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio.'),
            'year.required' => __('El año es obligatorio.'),
            'end_date.after_or_equal' => __('La fecha de fin debe ser posterior a la de inicio.'),
        ];
    }
}
