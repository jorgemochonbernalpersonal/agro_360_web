<?php

namespace App\Livewire\Sigpac;

use App\Models\SigpacCode;
use App\Models\Plot;
use App\Livewire\Concerns\WithToastNotifications;
use App\Rules\SigpacCodeFormat;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;

    public $plot_id = '';
    public $sigpacCodes = []; // Array para múltiples códigos

    public function mount()
    {
        // Si viene desde la vista de parcela, pre-seleccionar desde query string
        $plotId = request()->query('plot_id');
        if ($plotId) {
            $this->plot_id = $plotId;
        }
        
        // Inicializar con al menos un código
        $this->addSigpacCode();
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => 'required|exists:plots,id',
            'sigpacCodes' => 'required|array|min:1',
        ];

        // Validar cada código SIGPAC
        foreach ($this->sigpacCodes as $index => $code) {
            $rules["sigpacCodes.{$index}.code"] = [
                'required',
                'string',
                new SigpacCodeFormat(),
                function ($attribute, $value, $fail) use ($index) {
                    // Verificar que no exista ya
                    try {
                        $parsed = SigpacCode::parseSigpacCode($value);
                        $exists = SigpacCode::where('code', $parsed['code'])->exists();
                        if ($exists) {
                            $fail("El código SIGPAC {$value} ya existe.");
                        }
                    } catch (\InvalidArgumentException $e) {
                        // La validación de formato ya se hace con la regla
                    }
                }
            ];
        }

        return $rules;
    }

    public function addSigpacCode()
    {
        $this->sigpacCodes[] = [
            'code' => '',
        ];
    }

    public function removeSigpacCode($index)
    {
        unset($this->sigpacCodes[$index]);
        $this->sigpacCodes = array_values($this->sigpacCodes);
    }

    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $plot = Plot::findOrFail($this->plot_id);
            
            // Verificar permisos
            if (!Auth::user()->can('update', $plot)) {
                throw new \Exception('No tienes permisos para asociar códigos SIGPAC a esta parcela.');
            }
            
            $createdCodes = [];
            
            foreach ($this->sigpacCodes as $sigpacData) {
                // Parsear el código y extraer todos los campos
                $parsed = SigpacCode::parseSigpacCode($sigpacData['code']);
                
                // Verificar que no exista (doble verificación)
                $exists = SigpacCode::where('code', $parsed['code'])->exists();
                if ($exists) {
                    throw new \Exception("El código {$sigpacData['code']} ya existe.");
                }
                
                // Crear el código SIGPAC con todos los campos parseados
                $sigpacCode = SigpacCode::create($parsed);
                
                // Asociar con la parcela
                $plot->sigpacCodes()->attach($sigpacCode->id);
                $createdCodes[] = $sigpacCode;
            }
            
            DB::commit();
            
            $count = count($createdCodes);
            $message = $count === 1 
                ? 'Código SIGPAC creado correctamente.'
                : "{$count} códigos SIGPAC creados correctamente.";
            
            $this->toastSuccess($message);
            
            return $this->redirect(route('plots.show', $plot->id));
            
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            $this->toastError('Error de formato: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError('Error: ' . $e->getMessage());
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

