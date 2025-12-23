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

    public $plot_id = '';
    public $sigpacCodes = []; // Array para múltiples códigos con campos individuales

    public function mount()
    {
        // Si viene desde la vista de parcela, pre-seleccionar desde query string
        $plotId = request()->query('plot_id');
        if ($plotId) {
            $this->plot_id = $plotId;
        }
        
        // Inicializar con al menos un código
        $this->addSigpacCode();
        
        // Si hay parcela seleccionada, auto-rellenar
        if ($this->plot_id) {
            $this->updatedPlotId($this->plot_id);
        }
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => 'required|exists:plots,id',
            'sigpacCodes' => 'required|array|min:1',
        ];

        // Validar cada código SIGPAC
        foreach ($this->sigpacCodes as $index => $code) {
            // Validar campos individuales
            $rules["sigpacCodes.{$index}.code_autonomous_community"] = ['required', 'string', 'size:2', 'regex:/^\d{2}$/'];
            $rules["sigpacCodes.{$index}.code_province"] = ['required', 'string', 'size:2', 'regex:/^\d{2}$/'];
            $rules["sigpacCodes.{$index}.code_municipality"] = ['required', 'string', 'size:3', 'regex:/^\d{3}$/'];
            $rules["sigpacCodes.{$index}.code_aggregate"] = ['nullable', 'string', 'size:1', 'regex:/^\d{1}$/'];
            $rules["sigpacCodes.{$index}.code_zone"] = ['required', 'string', 'size:1', 'regex:/^\d{1}$/'];
            $rules["sigpacCodes.{$index}.code_polygon"] = ['required', 'string', 'size:2', 'regex:/^\d{2}$/'];
            $rules["sigpacCodes.{$index}.code_plot"] = ['required', 'string', 'size:5', 'regex:/^\d{5}$/'];
            $rules["sigpacCodes.{$index}.code_enclosure"] = ['required', 'string', 'size:3', 'regex:/^\d{3}$/'];

            // Validar que el código completo no exista ya en la base de datos
            $rules["sigpacCodes.{$index}"] = [
                function ($attribute, $value, $fail) use ($index) {
                    try {
                        $fullCode = SigpacCode::buildCodeFromFields($value);
                        $exists = SigpacCode::where('code', $fullCode)->exists();
                        if ($exists) {
                            $fail("El código SIGPAC completo ya existe en la base de datos.");
                        }
                    } catch (\Exception $e) {
                        $fail("Error al validar el código: " . $e->getMessage());
                    }
                }
            ];

            // Validar que no haya duplicados dentro del mismo formulario
            // No puede haber dos códigos con el mismo Polígono + Parcela + Recinto
            $rules["sigpacCodes.{$index}.duplicate_check"] = [
                function ($attribute, $value, $fail) use ($index) {
                    $code = $this->sigpacCodes[$index] ?? [];
                    $polygon = $code['code_polygon'] ?? '';
                    $plot = $code['code_plot'] ?? '';
                    $enclosure = $code['code_enclosure'] ?? '';
                    
                    // Solo validar si todos los campos están completos
                    if (strlen($polygon) === 2 && strlen($plot) === 5 && strlen($enclosure) === 3) {
                        // Buscar duplicados en otros códigos del formulario
                        foreach ($this->sigpacCodes as $otherIndex => $otherCode) {
                            if ($otherIndex !== $index) {
                                $otherPolygon = $otherCode['code_polygon'] ?? '';
                                $otherPlot = $otherCode['code_plot'] ?? '';
                                $otherEnclosure = $otherCode['code_enclosure'] ?? '';
                                
                                // Si todos los campos están completos y coinciden
                                if (strlen($otherPolygon) === 2 && 
                                    strlen($otherPlot) === 5 && 
                                    strlen($otherEnclosure) === 3 &&
                                    $polygon === $otherPolygon &&
                                    $plot === $otherPlot &&
                                    $enclosure === $otherEnclosure) {
                                    $fail("No puedes tener dos códigos SIGPAC con el mismo Polígono ({$polygon}), Parcela ({$plot}) y Recinto ({$enclosure}). Al menos uno de estos campos debe ser diferente.");
                                }
                            }
                        }
                    }
                }
            ];
        }

        return $rules;
    }

    public function addSigpacCode()
    {
        $newCode = [
            'code_autonomous_community' => '',
            'code_province' => '',
            'code_municipality' => '',
            'code_aggregate' => '0',
            'code_zone' => '',
            'code_polygon' => '',
            'code_plot' => '',
            'code_enclosure' => '',
        ];
        
        // Si hay parcela seleccionada, auto-rellenar
        if ($this->plot_id) {
            $plot = Plot::with(['autonomousCommunity', 'province', 'municipality'])
                ->find($this->plot_id);
            
            if ($plot && $plot->autonomousCommunity && $plot->province && $plot->municipality) {
                $newCode['code_autonomous_community'] = str_pad(
                    $plot->autonomousCommunity->code ?? '', 2, '0', STR_PAD_LEFT
                );
                $newCode['code_province'] = str_pad(
                    $plot->province->code ?? '', 2, '0', STR_PAD_LEFT
                );
                // Para municipio: el código es de 5 dígitos (28079), pero SIGPAC necesita solo los últimos 3 (079)
                $municipalityFullCode = $plot->municipality->code ?? '';
                $newCode['code_municipality'] = str_pad(
                    substr($municipalityFullCode, -3), 3, '0', STR_PAD_LEFT
                );
            }
        }
        
        $this->sigpacCodes[] = $newCode;
    }

    public function removeSigpacCode($index)
    {
        unset($this->sigpacCodes[$index]);
        $this->sigpacCodes = array_values($this->sigpacCodes);
    }

    /**
     * Auto-rellenar códigos cuando se selecciona una parcela
     */
    public function updatedPlotId($value)
    {
        if ($value) {
            // Cargar la parcela con sus relaciones
            $plot = Plot::with(['autonomousCommunity', 'province', 'municipality'])
                ->find($value);
            
            if ($plot && $plot->autonomousCommunity && $plot->province && $plot->municipality) {
                // Acceder directamente al campo 'code' de cada modelo
                $caCode = str_pad($plot->autonomousCommunity->code ?? '', 2, '0', STR_PAD_LEFT);
                $provinceCode = str_pad($plot->province->code ?? '', 2, '0', STR_PAD_LEFT);
                
                // Para municipio: el código es de 5 dígitos (28079), pero SIGPAC necesita solo los últimos 3 (079)
                $municipalityFullCode = $plot->municipality->code ?? '';
                $municipalityCode = str_pad(substr($municipalityFullCode, -3), 3, '0', STR_PAD_LEFT);
                
                // Auto-rellenar TODOS los códigos SIGPAC del formulario
                foreach ($this->sigpacCodes as $index => &$code) {
                    $code['code_autonomous_community'] = $caCode;
                    $code['code_province'] = $provinceCode;
                    $code['code_municipality'] = $municipalityCode;
                }
                unset($code); // Importante: liberar la referencia
            }
        }
    }

    /**
     * Construir código completo desde campos individuales
     */
    public function getFullCode($index): string
    {
        if (!isset($this->sigpacCodes[$index])) {
            return '';
        }
        
        try {
            return SigpacCode::buildCodeFromFields($this->sigpacCodes[$index]);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Verificar si un código está completo y válido
     */
    public function isCodeValid($index): bool
    {
        if (!isset($this->sigpacCodes[$index])) {
            return false;
        }

        $code = $this->sigpacCodes[$index];
        
        // Verificar que todos los campos requeridos estén llenos
        $required = ['code_autonomous_community', 'code_province', 'code_municipality', 
                    'code_zone', 'code_polygon', 'code_plot', 'code_enclosure'];
        
        foreach ($required as $field) {
            if (empty($code[$field] ?? '')) {
                return false;
            }
        }

        // Verificar longitudes
        if (strlen($code['code_autonomous_community'] ?? '') !== 2) return false;
        if (strlen($code['code_province'] ?? '') !== 2) return false;
        if (strlen($code['code_municipality'] ?? '') !== 3) return false;
        if (strlen($code['code_zone'] ?? '') !== 1) return false;
        if (strlen($code['code_polygon'] ?? '') !== 2) return false;
        if (strlen($code['code_plot'] ?? '') !== 5) return false;
        if (strlen($code['code_enclosure'] ?? '') !== 3) return false;

        return true;
    }

    /**
     * Verificar si hay duplicados en el formulario
     */
    public function hasDuplicate($index): bool
    {
        if (!isset($this->sigpacCodes[$index])) {
            return false;
        }

        $code = $this->sigpacCodes[$index];
        $polygon = $code['code_polygon'] ?? '';
        $plot = $code['code_plot'] ?? '';
        $enclosure = $code['code_enclosure'] ?? '';

        // Solo validar si todos los campos están completos
        if (strlen($polygon) !== 2 || strlen($plot) !== 5 || strlen($enclosure) !== 3) {
            return false;
        }

        // Buscar duplicados en otros códigos
        foreach ($this->sigpacCodes as $otherIndex => $otherCode) {
            if ($otherIndex !== $index) {
                $otherPolygon = $otherCode['code_polygon'] ?? '';
                $otherPlot = $otherCode['code_plot'] ?? '';
                $otherEnclosure = $otherCode['code_enclosure'] ?? '';

                if (strlen($otherPolygon) === 2 && 
                    strlen($otherPlot) === 5 && 
                    strlen($otherEnclosure) === 3 &&
                    $polygon === $otherPolygon &&
                    $plot === $otherPlot &&
                    $enclosure === $otherEnclosure) {
                    return true;
                }
            }
        }

        return false;
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
            
            // Validar duplicados final antes de guardar
            $polygonPlotEnclosure = [];
            foreach ($this->sigpacCodes as $index => $sigpacData) {
                $polygon = $sigpacData['code_polygon'] ?? '';
                $plotCode = $sigpacData['code_plot'] ?? '';
                $enclosure = $sigpacData['code_enclosure'] ?? '';
                
                $key = "{$polygon}-{$plotCode}-{$enclosure}";
                if (isset($polygonPlotEnclosure[$key])) {
                    throw new \Exception("No puedes tener dos códigos SIGPAC con el mismo Polígono ({$polygon}), Parcela ({$plotCode}) y Recinto ({$enclosure}).");
                }
                $polygonPlotEnclosure[$key] = true;
            }
            
            $createdCodes = [];
            
            foreach ($this->sigpacCodes as $sigpacData) {
                // Construir el código completo desde los campos individuales
                $fullCode = SigpacCode::buildCodeFromFields($sigpacData);
                
                // Verificar que no exista en la base de datos (doble verificación)
                $exists = SigpacCode::where('code', $fullCode)->exists();
                if ($exists) {
                    throw new \Exception("El código SIGPAC {$fullCode} ya existe en la base de datos.");
                }
                
                // Preparar datos para crear
                $dataToCreate = [
                    'code' => $fullCode,
                    'code_autonomous_community' => $sigpacData['code_autonomous_community'],
                    'code_province' => $sigpacData['code_province'],
                    'code_municipality' => $sigpacData['code_municipality'],
                    'code_aggregate' => $sigpacData['code_aggregate'] ?? '0',
                    'code_zone' => $sigpacData['code_zone'],
                    'code_polygon' => $sigpacData['code_polygon'],
                    'code_plot' => $sigpacData['code_plot'],
                    'code_enclosure' => $sigpacData['code_enclosure'],
                ];
                
                // Crear el código SIGPAC
                $sigpacCode = SigpacCode::create($dataToCreate);
                
                // Asociar con la parcela
                $plot->sigpacCodes()->attach($sigpacCode->id);
                $createdCodes[] = $sigpacCode;
            }
            
            DB::commit();
            
            $count = count($createdCodes);
            $message = $count === 1 
                ? 'Código SIGPAC creado correctamente.'
                : "{$count} códigos SIGPAC creados correctamente.";
            
            session()->flash('message', $message);
            
            return $this->redirect(route('sigpac.codes'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
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
