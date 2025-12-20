<?php

namespace App\Livewire\Plots;

use App\Models\Plot;
use App\Models\SigpacUse;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithWineryFilter;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Edit extends Component
{
    use WithRoleBasedFields, WithWineryFilter, WithToastNotifications;

    public Plot $plot;
    public $name = '';
    public $description = '';
    public $viticulturist_id = '';
    public $area = '';
    public $active = true;
    public $autonomous_community_id = '';
    public $province_id = '';
    public $municipality_id = '';
    public $sigpac_use = [];

    public function mount(Plot $plot)
    {
        if (!Auth::user()->can('update', $plot)) {
            abort(403);
        }

        $this->plot = $plot->load([
            'sigpacUses',
            'autonomousCommunity',
            'province',
            'municipality',
        ]);

        $this->name = $plot->name;
        $this->description = $plot->description;
        $this->viticulturist_id = $plot->viticulturist_id;
        $this->area = $plot->area;
        $this->active = $plot->active;
        $this->autonomous_community_id = $plot->autonomous_community_id;
        $this->province_id = $plot->province_id;
        $this->municipality_id = $plot->municipality_id;
        $this->sigpac_use = $plot->sigpacUses->pluck('id')->toArray();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ];

        // `winery_id` removed: do not validate here.

        // Viticultor es requerido si el usuario tiene rol que puede seleccionar viticultores
        if (in_array(Auth::user()->role, ['admin', 'supervisor', 'winery', 'viticulturist'])) {
            $rules['viticulturist_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
        }

        if ($this->canSelectSigpac()) {
            $rules['sigpac_use'] = 'required|array|min:1';
            $rules['sigpac_use.*'] = 'exists:sigpac_use,id';
        }

        return $rules;
    }

    public function updatedAutonomousCommunityId($value)
    {
        // Resetear provincia y municipio cuando cambia la comunidad autónoma
        // Si la provincia actual no pertenece a la nueva comunidad, resetear
        if ($this->province_id) {
            $currentProvince = Province::find($this->province_id);
            if ($currentProvince && $currentProvince->autonomous_community_id != $value) {
                $this->province_id = '';
                $this->municipality_id = '';
            }
        } else {
            $this->province_id = '';
            $this->municipality_id = '';
        }
    }

    public function updatedProvinceId($value)
    {
        // Resetear municipio cuando cambia la provincia
        // Si el municipio actual no pertenece a la nueva provincia, resetear
        if ($this->municipality_id) {
            $currentMunicipality = Municipality::find($this->municipality_id);
            if ($currentMunicipality && $currentMunicipality->province_id != $value) {
                $this->municipality_id = '';
            }
        } else {
            $this->municipality_id = '';
        }
    }

    public function update()
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

            // `winery_id` removed: plots now tracked by `viticulturist_id`.

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
            }

            if ($this->canSelectLocation()) {
                $data['autonomous_community_id'] = $this->autonomous_community_id;
                $data['province_id'] = $this->province_id;
                $data['municipality_id'] = $this->municipality_id;
            }

            $this->plot->update($data);

            // Sincronizar relaciones many-to-many
            if ($this->canSelectSigpac() && !empty($this->sigpac_use)) {
                $this->plot->sigpacUses()->sync($this->sigpac_use);
            }

            DB::commit();

            $this->toastSuccess('Parcela actualizada correctamente.');
            return $this->redirect(route('plots.index'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar parcela: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'plot_id' => $this->plot->id,
                'data' => $data ?? [],
                'exception' => $e
            ]);
            
            throw ValidationException::withMessages([
                'general' => 'Error al actualizar la parcela. Por favor, intenta de nuevo.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.plots.edit', [
            'sigpacUses' => SigpacUse::orderBy('code')->get(),
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
