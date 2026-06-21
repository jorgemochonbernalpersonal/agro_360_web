<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Edit extends AbstractEdit
{
    public Campaign $campaign;

    public string $name = '';

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $description = '';

    public function mount(Campaign $campaign): void
    {
        $this->authorizeOwnership($campaign);
        abort_if($campaign->locked_at !== null, 403);

        $this->campaign = $campaign;
        $this->name = $campaign->name;
        $this->year = (string) $campaign->year;
        $this->start_date = $campaign->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $campaign->end_date?->format('Y-m-d') ?? '';
        $this->description = $campaign->description ?? '';
    }

    protected function ownerColumn(): string
    {
        return 'viticulturist_id';
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

    protected function performUpdate(): void
    {
        if (
            Campaign::forViticulturist(Auth::id())
                ->forYear((int) $this->year)
                ->where('id', '!=', $this->campaign->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'year' => __('Ya existe otra campaña para el año :year.', ['year' => $this->year]),
            ]);
        }

        $this->campaign->update([
            'name' => $this->name,
            'year' => (int) $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'description' => $this->description ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return "Campaña {$this->campaign->year} actualizada correctamente.";
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
