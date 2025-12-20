<?php

namespace App\Livewire\Sigpac;

use App\Models\SigpacCode;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public SigpacCode $sigpacCode;
    public $code = '';
    public $code_polygon = '';
    public $code_plot = '';
    public $code_enclosure = '';
    public $code_aggregate = '';
    public $code_province = '';
    public $code_zone = '';
    public $code_municipality = '';

    public function mount(SigpacCode $code)
    {
        $this->sigpacCode = $code;
        $this->code = $code->code;
        $this->code_polygon = $code->code_polygon;
        $this->code_plot = $code->code_plot;
        $this->code_enclosure = $code->code_enclosure;
        $this->code_aggregate = $code->code_aggregate;
        $this->code_province = $code->code_province;
        $this->code_zone = $code->code_zone;
        $this->code_municipality = $code->code_municipality;
    }

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:sigpac_code,code,' . $this->sigpacCode->id,
            'code_polygon' => 'nullable|string|max:10',
            'code_plot' => 'nullable|string|max:10',
            'code_enclosure' => 'nullable|string|max:10',
            'code_aggregate' => 'nullable|string|max:10',
            'code_province' => 'nullable|string|max:10',
            'code_zone' => 'nullable|string|max:10',
            'code_municipality' => 'nullable|string|max:10',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->sigpacCode->update([
            'code' => $this->code,
            'code_polygon' => $this->code_polygon ?: null,
            'code_plot' => $this->code_plot ?: null,
            'code_enclosure' => $this->code_enclosure ?: null,
            'code_aggregate' => $this->code_aggregate ?: null,
            'code_province' => $this->code_province ?: null,
            'code_zone' => $this->code_zone ?: null,
            'code_municipality' => $this->code_municipality ?: null,
        ]);

        $this->toastSuccess('Código SIGPAC actualizado correctamente.');
        return $this->redirect(route('sigpac.codes'));
    }

    public function render()
    {
        return view('livewire.sigpac.edit')->layout('layouts.app');
    }
}

