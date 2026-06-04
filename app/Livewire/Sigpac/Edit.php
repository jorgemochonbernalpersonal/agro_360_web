<?php

namespace App\Livewire\Sigpac;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Plot;
use App\Models\SigpacCode;
use App\Models\SigpacUse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Edit extends Component
{
    use WithToastNotifications;

    public SigpacCode $sigpacCode;

    public $plot_id = '';

    public $sigpac_use = [];

    public $sigpacCodes = []; // Array para un solo código (estructura igual a Create)

    public function mount(SigpacCode $code)
    {
        $this->sigpacCode = $code->load(['plots.sigpacUses']);

        // Obtener la parcela asociada (primera parcela)
        $firstPlot = $code->plots->first();
        if ($firstPlot) {
            $this->plot_id = $firstPlot->id;
            $this->sigpac_use = $firstPlot->sigpacUses->pluck('id')->toArray();
        }

        // Inicializar el array con el código existente
        $this->sigpacCodes = [[
            'code_autonomous_community' => $code->code_autonomous_community ?? '',
            'code_province' => $code->code_province ?? '',
            'code_municipality' => $code->code_municipality ?? '',
            'code_aggregate' => $code->code_aggregate ?? '0',
            'code_zone' => $code->code_zone ?? '',
            'code_polygon' => $code->code_polygon ?? '',
            'code_plot' => $code->code_plot ?? '',
            'code_enclosure' => $code->code_enclosure ?? '',
        ]];
    }

    /**
     * Auto-rellenar códigos cuando se selecciona una parcela
     *
     * @param mixed $value
     */
    public function updatedPlotId($value)
    {
        if ($value) {
            $plotForUses = Plot::with('sigpacUses')->find($value);
            $this->sigpac_use = $plotForUses?->sigpacUses->pluck('id')->toArray() ?? [];

            // Cargar la parcela con sus relaciones
            $plot = Plot::with(['autonomousCommunity', 'province', 'municipality'])
                ->find($value);

            if ($plot && $plot->autonomousCommunity && $plot->province && $plot->municipality) {
                // Acceder directamente al campo 'code' de cada modelo
                $caCode = str_pad($plot->autonomousCommunity->code ?? '', 2, '0', STR_PAD_LEFT);
                $provinceCode = str_pad($plot->province->code ?? '', 2, '0', STR_PAD_LEFT);

                // Municipio: NO autocompletar, dejar vacío para entrada manual
                // $municipalityFullCode = $plot->municipality->code ?? '';
                // $municipalityCode = str_pad(substr($municipalityFullCode, -3), 3, '0', STR_PAD_LEFT);

                // Auto-rellenar SOLO CA y Provincia en el código SIGPAC
                if (isset($this->sigpacCodes[0])) {
                    $this->sigpacCodes[0]['code_autonomous_community'] = $caCode;
                    $this->sigpacCodes[0]['code_province'] = $provinceCode;
                    // NO autocompletar municipio
                    // $this->sigpacCodes[0]['code_municipality'] = $municipalityCode;
                }
            }
        }
    }

    /**
     * Construir código completo desde campos individuales
     *
     * @param mixed $index
     */
    public function getFullCode($index): string
    {
        if (! isset($this->sigpacCodes[$index])) {
            return '';
        }

        try {
            return SigpacCode::buildCodeFromFields($this->sigpacCodes[$index]);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Rellenar todos los campos de un código SIGPAC desde Alpine (pegar referencia completa).
     */
    public function fillSigpacCode(int $index, array $fields): void
    {
        if (! isset($this->sigpacCodes[$index])) {
            return;
        }

        $codes = $this->sigpacCodes;
        $codes[$index] = array_merge($codes[$index], array_intersect_key($fields, $codes[$index]));
        $this->sigpacCodes = $codes;
    }

    /**
     * Verificar si un código está completo y válido
     *
     * @param mixed $index
     */
    public function isCodeValid($index): bool
    {
        if (! isset($this->sigpacCodes[$index])) {
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
        if (strlen($code['code_autonomous_community'] ?? '') !== 2) {
            return false;
        }
        if (strlen($code['code_province'] ?? '') !== 2) {
            return false;
        }
        if (strlen($code['code_municipality'] ?? '') !== 3) {
            return false;
        }
        if (empty($code['code_zone']) || strlen($code['code_zone']) > 3) {
            return false;
        }
        if (empty($code['code_polygon']) || strlen($code['code_polygon']) > 3) {
            return false;
        }
        if (strlen($code['code_plot'] ?? '') !== 5) {
            return false;
        }
        if (strlen($code['code_enclosure'] ?? '') !== 3) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si hay duplicados (en edición siempre será false ya que solo hay un código)
     *
     * @param mixed $index
     */
    public function hasDuplicate($index): bool
    {
        return false; // En edición solo hay un código, no puede haber duplicados
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $plot = Plot::findOrFail($this->plot_id);

            // Verificar permisos
            if (! Auth::user()->can('update', $plot)) {
                throw new \Exception(__('No tienes permisos para modificar códigos SIGPAC de esta parcela.'));
            }

            $sigpacData = $this->sigpacCodes[0];

            // Construir el código completo desde los campos individuales
            $fullCode = SigpacCode::buildCodeFromFields($sigpacData);

            // Verificar que no exista en la base de datos (excepto el actual)
            $exists = SigpacCode::where('code', $fullCode)
                ->where('id', '!=', $this->sigpacCode->id)
                ->exists();
            if ($exists) {
                throw new \Exception("El código SIGPAC {$fullCode} ya existe en la base de datos.");
            }

            // Actualizar el código SIGPAC
            $this->sigpacCode->update([
                'code' => $fullCode,
                'code_autonomous_community' => $sigpacData['code_autonomous_community'],
                'code_province' => $sigpacData['code_province'],
                'code_municipality' => $sigpacData['code_municipality'],
                'code_aggregate' => $sigpacData['code_aggregate'] ?? '0',
                'code_zone' => $sigpacData['code_zone'],
                'code_polygon' => $sigpacData['code_polygon'],
                'code_plot' => $sigpacData['code_plot'],
                'code_enclosure' => $sigpacData['code_enclosure'],
            ]);

            // Sincronizar usos SIGPAC con la parcela
            $plot->sigpacUses()->sync($this->sigpac_use ?? []);

            // Actualizar la asociación con la parcela si cambió
            if ($this->sigpacCode->plots->first()?->id !== $plot->id) {
                // Desasociar de la parcela anterior
                $this->sigpacCode->plots()->detach();
                // Asociar con la nueva parcela
                $plot->sigpacCodes()->attach($this->sigpacCode->id);
            }

            DB::commit();

            $this->toastSuccess(__('Código SIGPAC actualizado correctamente.'));

            return $this->redirect(route('sigpac.codes'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toastError(__('Error: :error', ['error' => $e->getMessage()]));
        }
    }

    public function render()
    {
        $user = Auth::user();
        $plots = Plot::forUser($user)->get();

        return view('livewire.sigpac.edit', [
            'plots' => $plots,
            'sigpacUses' => SigpacUse::select(['id', 'code', 'description'])->orderBy('code')->get(),
        ]);
    }

    protected function rules(): array
    {
        $rules = [
            'plot_id' => 'required|exists:plots,id',
            'sigpac_use' => 'nullable|array',
            'sigpac_use.*' => 'exists:sigpac_use,id',
            'sigpacCodes' => 'required|array|min:1',
        ];

        // Validar el código SIGPAC (solo uno en edición)
        $index = 0;
        $code = $this->sigpacCodes[$index] ?? [];

        // Validar campos individuales
        $rules["sigpacCodes.{$index}.code_autonomous_community"] = ['required', 'string', 'size:2', 'regex:/^\d{2}$/'];
        $rules["sigpacCodes.{$index}.code_province"] = ['required', 'string', 'size:2', 'regex:/^\d{2}$/'];
        $rules["sigpacCodes.{$index}.code_municipality"] = ['required', 'string', 'size:3', 'regex:/^\d{3}$/'];
        $rules["sigpacCodes.{$index}.code_aggregate"] = ['nullable', 'string', 'max:3', 'regex:/^\d{1,3}$/'];
        $rules["sigpacCodes.{$index}.code_zone"] = ['required', 'string', 'max:3', 'regex:/^\d{1,3}$/'];
        $rules["sigpacCodes.{$index}.code_polygon"] = ['required', 'string', 'max:3', 'regex:/^\d{1,3}$/'];
        $rules["sigpacCodes.{$index}.code_plot"] = ['required', 'string', 'size:5', 'regex:/^\d{5}$/'];
        $rules["sigpacCodes.{$index}.code_enclosure"] = ['required', 'string', 'size:3', 'regex:/^\d{3}$/'];

        // Validar que el código completo no exista ya en la base de datos (excepto el actual)
        $rules["sigpacCodes.{$index}"] = [
            function ($attribute, $value, $fail) {
                try {
                    $fullCode = SigpacCode::buildCodeFromFields($value);
                    $exists = SigpacCode::where('code', $fullCode)
                        ->where('id', '!=', $this->sigpacCode->id)
                        ->exists();
                    if ($exists) {
                        $fail('El código SIGPAC completo ya existe en la base de datos.');
                    }
                } catch (\Exception $e) {
                    $fail('Error al validar el código: '.$e->getMessage());
                }
            },
        ];

        return $rules;
    }
}
