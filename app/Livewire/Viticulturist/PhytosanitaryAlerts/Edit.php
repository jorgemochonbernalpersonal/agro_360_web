<?php

namespace App\Livewire\Viticulturist\PhytosanitaryAlerts;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\PhytosanitaryAlert;

class Edit extends AbstractEdit
{
    public PhytosanitaryAlert $phytosanitaryAlert;

    public string $title = '';

    public string $source = 'otro';

    public string $alert_type = 'otro';

    public string $severity = 'media';

    public string $affected_area = '';

    public string $description = '';

    public string $recommendations = '';

    public string $alert_date = '';

    public string $expiry_date = '';

    public function mount(PhytosanitaryAlert $phytosanitaryAlert): void
    {
        $this->authorizeOwnership($phytosanitaryAlert);
        $this->phytosanitaryAlert = $phytosanitaryAlert;
        $this->title = $phytosanitaryAlert->title;
        $this->source = $phytosanitaryAlert->source;
        $this->alert_type = $phytosanitaryAlert->alert_type;
        $this->severity = $phytosanitaryAlert->severity;
        $this->affected_area = $phytosanitaryAlert->affected_area ?? '';
        $this->description = $phytosanitaryAlert->description;
        $this->recommendations = $phytosanitaryAlert->recommendations ?? '';
        $this->alert_date = $phytosanitaryAlert->alert_date->format('Y-m-d');
        $this->expiry_date = $phytosanitaryAlert->expiry_date?->format('Y-m-d') ?? '';
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

    protected function performUpdate(): void
    {
        $this->phytosanitaryAlert->update([
            'title' => $this->title,
            'source' => $this->source,
            'alert_type' => $this->alert_type,
            'severity' => $this->severity,
            'affected_area' => $this->affected_area ?: null,
            'description' => $this->description,
            'recommendations' => $this->recommendations ?: null,
            'alert_date' => $this->alert_date,
            'expiry_date' => $this->expiry_date ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Alerta fitosanitaria actualizada correctamente.');
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
