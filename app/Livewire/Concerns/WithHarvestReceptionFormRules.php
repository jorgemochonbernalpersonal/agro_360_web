<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

trait WithHarvestReceptionFormRules
{
    /**
     * Las 23 reglas compartidas por Create y Edit de recepción de vendimia.
     * Create añade viticulturist_id/plot_id/plot_planting_id/vintage_year
     * y endurece disqualified_reason a required_if.
     */
    protected function receptionBaseRules(): array
    {
        return [
            'harvest_start_date' => ['required', 'date'],
            'harvest_time' => ['nullable', 'date_format:H:i'],
            'harvest_ticket_number' => ['nullable', 'string', 'max:50'],
            'total_weight' => ['required', 'numeric', 'min:0.01'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0'],
            'baume_degree' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree' => ['nullable', 'numeric', 'min:0', 'max:40'],
            'acidity_level' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level' => ['nullable', 'numeric', 'min:0', 'max:14'],
            'potential_alcohol' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'health_status' => ['nullable', 'in:sano,daño_leve,daño_moderado,daño_grave'],
            'sanitary_state_grapes' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_agraces' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_botrytis' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_oidium' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_mildew' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transport_document_number' => ['nullable', 'string', 'max:50'],
            'destination_rega_code' => ['nullable', 'string', 'max:20'],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'container_id' => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())->where('unit', 'kg')],
            'disqualified' => ['boolean'],
            'disqualified_reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function receptionBaseMessages(): array
    {
        return [
            'harvest_start_date.required' => __('La fecha de recepción es obligatoria.'),
            'total_weight.required' => __('El peso recibido es obligatorio.'),
            'total_weight.min' => __('El peso debe ser mayor que 0.'),
            'container_id.required' => __('Selecciona un depósito de destino.'),
        ];
    }
}
