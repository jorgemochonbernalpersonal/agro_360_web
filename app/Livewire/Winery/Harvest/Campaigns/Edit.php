<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Campaign $campaign;

    public string $name = '';

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public string $description = '';

    public function mount(Campaign $campaign): void
    {
        abort_if($campaign->viticulturist_id !== Auth::id(), 403);
        abort_if($campaign->locked_at !== null, 403);

        $this->campaign = $campaign;
        $this->name = $campaign->name;
        $this->year = (string) $campaign->year;
        $this->start_date = $campaign->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $campaign->end_date?->format('Y-m-d') ?? '';
        $this->description = $campaign->description ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $duplicate = Campaign::forViticulturist(Auth::id())
            ->forYear((int) $this->year)
            ->where('id', '!=', $this->campaign->id)
            ->exists();

        if ($duplicate) {
            $this->addError('year', __('Ya existe otra campaña para el año :year.', ['year' => $this->year]));

            return;
        }

        $this->campaign->update([
            'name' => $this->name,
            'year' => (int) $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'description' => $this->description ?: null,
        ]);

        $this->toastSuccess("Campaña {$this->campaign->year} actualizada correctamente.");
        $this->redirect(roleRoute('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.harvest.campaigns.edit')
            ->layout('layouts.app');
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

    protected function messages(): array
    {
        return [
            'name.required' => __('El nombre es obligatorio.'),
            'year.required' => __('El año es obligatorio.'),
            'end_date.after_or_equal' => __('La fecha de fin debe ser posterior a la de inicio.'),
        ];
    }
}
