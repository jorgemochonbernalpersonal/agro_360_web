<?php

namespace App\Livewire\Concerns;

use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;

trait WithSettingsFieldbook
{
    public $default_limit_kg_per_ha = '';

    public $degree_day_base = 10;

    public $document_prefix_activity = 'ACT';

    public $document_prefix_harvest = 'VND';

    public $legal_text_fieldbook = '';

    public $notify_harvest_alerts = true;

    public $notify_activity_alerts = true;

    public function loadFieldbook(): void
    {
        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $this->default_limit_kg_per_ha = $settings->default_limit_kg_per_ha ?? '';
        $this->degree_day_base = $settings->degree_day_base;
        $this->document_prefix_activity = $settings->document_prefix_activity;
        $this->document_prefix_harvest = $settings->document_prefix_harvest;
        $this->legal_text_fieldbook = $settings->legal_text_fieldbook ?? '';
        $this->notify_harvest_alerts = $settings->notify_harvest_alerts;
        $this->notify_activity_alerts = $settings->notify_activity_alerts;
    }

    public function saveFieldbook(): void
    {
        $this->validate([
            'default_limit_kg_per_ha' => 'nullable|numeric|min:0|max:999999',
            'degree_day_base' => 'required|numeric|min:0|max:30',
            'document_prefix_activity' => 'required|string|max:20|regex:/^[A-Z0-9_\-]+$/',
            'document_prefix_harvest' => 'required|string|max:20|regex:/^[A-Z0-9_\-]+$/',
            'legal_text_fieldbook' => 'nullable|string|max:2000',
        ], [
            'document_prefix_activity.regex' => __('Solo letras mayúsculas, números, guión y guión bajo.'),
            'document_prefix_harvest.regex' => __('Solo letras mayúsculas, números, guión y guión bajo.'),
        ]);

        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $settings->update([
            'default_limit_kg_per_ha' => $this->default_limit_kg_per_ha ?: null,
            'degree_day_base' => $this->degree_day_base,
            'document_prefix_activity' => strtoupper($this->document_prefix_activity),
            'document_prefix_harvest' => strtoupper($this->document_prefix_harvest),
            'legal_text_fieldbook' => $this->legal_text_fieldbook ?: null,
            'notify_harvest_alerts' => $this->notify_harvest_alerts,
            'notify_activity_alerts' => $this->notify_activity_alerts,
        ]);

        $this->loadFieldbook();
        $this->toastSuccess(__('Configuración del cuaderno guardada correctamente'));
    }
}
