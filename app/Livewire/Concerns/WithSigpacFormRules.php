<?php

namespace App\Livewire\Concerns;

trait WithSigpacFormRules
{
    protected function sigpacBaseRules(): array
    {
        return [
            'plot_id'      => 'required|exists:plots,id',
            'sigpac_use'   => 'nullable|array',
            'sigpac_use.*' => 'exists:sigpac_use,id',
            'sigpacCodes'  => 'required|array|min:1',
        ];
    }

    protected function sigpacCodeFieldRules(int $index): array
    {
        $p = "sigpacCodes.{$index}";

        return [
            "{$p}.code_autonomous_community" => ['required', 'string', 'size:2', 'regex:/^\d{2}$/'],
            "{$p}.code_province"             => ['required', 'string', 'size:2', 'regex:/^\d{2}$/'],
            "{$p}.code_municipality"         => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
            "{$p}.code_aggregate"            => ['nullable', 'string', 'max:3', 'regex:/^\d{1,3}$/'],
            "{$p}.code_zone"                 => ['required', 'string', 'max:3', 'regex:/^\d{1,3}$/'],
            "{$p}.code_polygon"              => ['required', 'string', 'max:3', 'regex:/^\d{1,3}$/'],
            "{$p}.code_plot"                 => ['required', 'string', 'size:5', 'regex:/^\d{5}$/'],
            "{$p}.code_enclosure"            => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
        ];
    }
}
