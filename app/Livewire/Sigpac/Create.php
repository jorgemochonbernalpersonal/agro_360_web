<?php

namespace App\Livewire\Sigpac;

use App\Models\SigpacCode;
use App\Models\Plot;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;

    public $code = '';
    public $code_polygon = '';
    public $code_plot = '';
    public $code_enclosure = '';
    public $code_aggregate = '';
    public $code_province = '';
    public $code_zone = '';
    public $code_municipality = '';
    public $plot_id = '';

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:sigpac_code,code',
            'code_polygon' => 'nullable|string|max:10',
            'code_plot' => 'nullable|string|max:10',
            'code_enclosure' => 'nullable|string|max:10',
            'code_aggregate' => 'nullable|string|max:10',
            'code_province' => 'nullable|string|max:10',
            'code_zone' => 'nullable|string|max:10',
            'code_municipality' => 'nullable|string|max:10',
            'plot_id' => 'nullable|exists:plots,id',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $sigpacCode = SigpacCode::create([
                'code' => $this->code,
                'code_polygon' => $this->code_polygon ?: null,
                'code_plot' => $this->code_plot ?: null,
                'code_enclosure' => $this->code_enclosure ?: null,
                'code_aggregate' => $this->code_aggregate ?: null,
                'code_province' => $this->code_province ?: null,
                'code_zone' => $this->code_zone ?: null,
                'code_municipality' => $this->code_municipality ?: null,
            ]);

            // Si se seleccionó una parcela, asociarla
            if ($this->plot_id) {
                $plot = Plot::findOrFail($this->plot_id);
                if (Auth::user()->can('update', $plot)) {
                    $plot->sigpacCodes()->attach($sigpacCode->id);
                }
            }

            DB::commit();

            $this->toastSuccess('Código SIGPAC creado correctamente.');
            
            if ($this->plot_id) {
                return $this->redirect(route('sigpac.geometry.edit-plot', [
                    'sigpacId' => $sigpacCode->id,
                    'plotId' => $this->plot_id
                ]));
            }
            
            return $this->redirect(route('sigpac.codes'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Error al crear el código SIGPAC: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $plots = Plot::forUser($user)->get();

        return view('livewire.sigpac.create', [
            'plots' => $plots,
        ])->layout('layouts.app');
    }
}

