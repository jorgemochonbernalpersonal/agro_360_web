<?php

namespace App\Livewire\Plots;

use App\Models\Plot;
use App\Models\SigpacUse;
use App\Models\SigpacCode;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\MultipartPlotSigpac;
use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithWineryFilter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Create extends Component
{
    use WithRoleBasedFields, WithWineryFilter;

    public $name = '';
    public $description = '';
    public $winery_id = '';
    public $viticulturist_id = '';
    public $area = '';
    public $active = true;
    public $autonomous_community_id = '';
    public $province_id = '';
    public $municipality_id = '';
    public $sigpac_use = [];
    public $sigpac_code = [];
    public $multipart_coordinates = []; // Array de coordenadas

    public function mount()
    {
        if (!Auth::user()->can('create', Plot::class)) {
            abort(403);
        }

        // Auto-asignar bodega si es winery
        if (Auth::user()->isWinery()) {
            $this->winery_id = Auth::id();
        }
        
        // Auto-asignar viticultor si es viticulturist
        // Siempre se auto-asigna el viticultor a sí mismo, a menos que pueda seleccionar otros viticultores
        if (Auth::user()->isViticulturist()) {
            if (!$this->canSelectViticulturist()) {
                // Si NO puede seleccionar viticultores (no tiene viticultores creados), se auto-asigna
                $this->viticulturist_id = Auth::id();
            }
            // Si puede seleccionar, dejamos el campo vacío para que elija
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ];

        // Validar solo si el campo es visible
        if ($this->canSelectWinery()) {
            $rules['winery_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectViticulturist()) {
            $rules['viticulturist_id'] = 'nullable|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
        }

        if ($this->canSelectSigpac()) {
            $rules['sigpac_use'] = 'required|array|min:1';
            $rules['sigpac_use.*'] = 'exists:sigpac_use,id';
            $rules['sigpac_code'] = 'required|array|min:1';
            $rules['sigpac_code.*'] = 'exists:sigpac_code,id';
        }

        return $rules;
    }

    public function updatedAutonomousCommunityId($value)
    {
        // Resetear provincia y municipio cuando cambia la comunidad autónoma
        $this->province_id = '';
        $this->municipality_id = '';
    }

    public function updatedProvinceId($value)
    {
        // Resetear municipio cuando cambia la provincia
        $this->municipality_id = '';
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'area' => $this->area ?: null,
                'active' => $this->active,
            ];

            // Solo agregar campos si son visibles
            if ($this->canSelectWinery()) {
                $data['winery_id'] = $this->winery_id;
            } elseif (Auth::user()->isWinery()) {
                $data['winery_id'] = Auth::id(); // Auto-asignar si es winery
            } elseif (Auth::user()->isViticulturist()) {
                // Para viticultores, obtener la primera winery asociada
                $wineries = Auth::user()->wineries;
                if ($wineries->isEmpty()) {
                    throw ValidationException::withMessages([
                        'general' => 'No tienes ninguna bodega asociada. Debes estar asociado a una bodega para crear parcelas. Por favor, contacta con tu administrador o supervisor.',
                    ]);
                }
                $data['winery_id'] = $wineries->first()->id;
            }

            if ($this->canSelectViticulturist() && $this->viticulturist_id) {
                // Validar que el viticultor fue creado por el usuario
                $user = Auth::user();
                $canAssign = false;
                
                if ($user->isWinery()) {
                    $canAssign = \App\Models\WineryViticulturist::where('viticulturist_id', $this->viticulturist_id)
                        ->where('winery_id', $user->id)
                        ->where('source', \App\Models\WineryViticulturist::SOURCE_OWN)
                        ->where('assigned_by', $user->id)
                        ->exists();
                } elseif ($user->isViticulturist()) {
                    $canAssign = $user->canEditViticulturist($this->viticulturist_id);
                } else {
                    $canAssign = true; // Admin y supervisor
                }
                
                if (!$canAssign) {
                    throw ValidationException::withMessages([
                        'viticulturist_id' => 'Solo puedes asignar parcelas a viticultores que has creado.',
                    ]);
                }
                
                $data['viticulturist_id'] = $this->viticulturist_id;
            } elseif (Auth::user()->isViticulturist()) {
                // Auto-asignar viticultor si es viticulturist y no puede seleccionar o no seleccionó ninguno
                $data['viticulturist_id'] = Auth::id();
            }

            if ($this->canSelectLocation()) {
                $data['autonomous_community_id'] = $this->autonomous_community_id;
                $data['province_id'] = $this->province_id;
                $data['municipality_id'] = $this->municipality_id;
            }

            $plot = Plot::create($data);

            // Sincronizar relaciones many-to-many
            if ($this->canSelectSigpac() && !empty($this->sigpac_use)) {
                $plot->sigpacUses()->sync($this->sigpac_use);
            }

            if ($this->canSelectSigpac() && !empty($this->sigpac_code)) {
                $plot->sigpacCodes()->sync($this->sigpac_code);
            }

            // Guardar coordenadas multiparte
            if (!empty($this->multipart_coordinates)) {
                foreach ($this->multipart_coordinates as $coords) {
                    if (!empty($coords['coordinates'])) {
                        MultipartPlotSigpac::create([
                            'plot_id' => $plot->id,
                            'coordinates' => $coords['coordinates'],
                            'sigpac_code_id' => $coords['sigpac_code_id'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'Parcela creada correctamente.');
            return $this->redirect(route('plots.index'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear parcela: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $data ?? [],
                'exception' => $e
            ]);
            
            throw ValidationException::withMessages([
                'general' => 'Error al crear la parcela. Por favor, intenta de nuevo.',
            ]);
        }
    }

    public function addCoordinate()
    {
        $this->multipart_coordinates[] = [
            'coordinates' => '',
            'sigpac_code_id' => null,
        ];
    }

    public function removeCoordinate($index)
    {
        unset($this->multipart_coordinates[$index]);
        $this->multipart_coordinates = array_values($this->multipart_coordinates);
    }

    public function render()
    {
        return view('livewire.plots.create', [
            'sigpacUses' => SigpacUse::orderBy('code')->get(),
            'sigpacCodes' => SigpacCode::orderBy('code')->get(),
            'autonomousCommunities' => AutonomousCommunity::orderBy('name')->get(),
            'provinces' => $this->autonomous_community_id 
                ? Province::where('autonomous_community_id', $this->autonomous_community_id)->orderBy('name')->get()
                : collect(),
            'municipalities' => $this->province_id
                ? Municipality::where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
        ])->layout('layouts.app');
    }
}
