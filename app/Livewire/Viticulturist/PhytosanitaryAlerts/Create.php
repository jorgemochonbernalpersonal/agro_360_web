<?php

namespace App\Livewire\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\PhytosanitaryAlert;

class Create extends AbstractCreate
{
    public string $title = '';

    public string $source = 'otro';

    public string $alert_type = 'otro';

    public string $severity = 'media';

    public string $affected_area = '';

    public string $description = '';

    public string $recommendations = '';

    public string $alert_date = '';

    public string $expiry_date = '';

    public function mount(): void
    {
        $this->alert_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'source' => 'required|in:'.implode(',', array_keys(PhytosanitaryAlert::SOURCES)),
            'alert_type' => 'required|in:'.implode(',', array_keys(PhytosanitaryAlert::ALERT_TYPES)),
            'severity' => 'required|in:'.implode(',', array_keys(PhytosanitaryAlert::SEVERITIES)),
            'affected_area' => 'nullable|string|max:255',
            'description' => 'required|string',
            'recommendations' => 'nullable|string',
            'alert_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:alert_date',
        ];
    }

    protected function performCreate(): void
    {
        PhytosanitaryAlert::create([
            'viticulturist_id' => $this->viticulturistId(),
            'title' => $this->title,
            'source' => $this->source,
            'alert_type' => $this->alert_type,
            'severity' => $this->severity,
            'affected_area' => $this->affected_area ?: null,
            'description' => $this->description,
            'recommendations' => $this->recommendations ?: null,
            'alert_date' => $this->alert_date,
            'expiry_date' => $this->expiry_date ?: null,
            'active' => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Alerta fitosanitaria registrada correctamente.');
    }

    protected function indexRoute(): string
    {
        return $this->rolePrefix().'.phytosanitary-alerts.index';
    }

    protected function viewData(): array
    {
        return [
            'sources' => PhytosanitaryAlert::sourceOptions(),
            'alertTypes' => PhytosanitaryAlert::alertTypeOptions(),
            'severities' => PhytosanitaryAlert::severityOptions(),
        ];
    }
}
