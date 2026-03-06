<?php

namespace App\Livewire\Plots;

use App\Models\Plot;
use App\Models\AutonomousCommunity;
use App\Models\SoilType;
use App\Models\IrrigationType;
use App\Models\Topography;
use App\Models\PropertyType;
use App\Models\Valley;
use App\Models\Site;
use App\Models\TrainingSystem;
use App\Models\Province;
use App\Models\Municipality;
use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Edit extends Component
{
    use WithRoleBasedFields, WithUserFilters, WithToastNotifications;

    public Plot $plot;
    public $name = '';
    public $description = '';
    public $viticulturist_id = '';
    public $area = '';
    public $active = true;
    public $autonomous_community_id = '';
    public $province_id = '';
    public $municipality_id = '';
    public $tenure_regime = 'propiedad';
    public $code_parcel = '';
    public $orientation = '';
    public $maximum_yield_kg_ha = '';
    public $degree_day_base = '';
    public $limit_kg = '';
    public $cadastral_area = '';
    public $unit_of_measurement = 'kg';
    public $plantation_year = '';
    public $is_organic = false;
    // Lookup FKs
    public $soil_type_id = '';
    public $irrigation_type_id = '';
    public $topography_id = '';
    public $property_type_id = '';
    public $valley_id = '';
    public $site_id = '';
    public $training_system_id = '';
    public $owner_id = '';
    // Nuevos campos simples
    public $enclosure = '';
    public $planting_pattern = '';
    public $slope = '';
    public $number_of_vines = '';

    public function mount(Plot $plot)
    {
        if (!Auth::user()->can('update', $plot)) {
            abort(403);
        }

        $this->plot = $plot->load([
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
        $this->tenure_regime = $plot->tenure_regime ?? 'propiedad';
        $this->code_parcel = $plot->code_parcel ?? '';
        $this->orientation = $plot->orientation ?? '';
        $this->maximum_yield_kg_ha = $plot->maximum_yield_kg_ha ?? '';
        $this->degree_day_base = $plot->degree_day_base ?? '';
        $this->limit_kg = $plot->limit_kg ?? '';
        $this->cadastral_area = $plot->cadastral_area ?? '';
        $this->unit_of_measurement = $plot->unit_of_measurement ?? 'kg';
        $this->plantation_year = $plot->plantation_year ?? '';
        $this->is_organic = $plot->is_organic ?? false;
        $this->soil_type_id = $plot->soil_type_id ?? '';
        $this->irrigation_type_id = $plot->irrigation_type_id ?? '';
        $this->topography_id = $plot->topography_id ?? '';
        $this->property_type_id = $plot->property_type_id ?? '';
        $this->valley_id = $plot->valley_id ?? '';
        $this->site_id = $plot->site_id ?? '';
        $this->training_system_id = $plot->training_system_id ?? '';
        $this->owner_id = $plot->owner_id ?? '';
        $this->enclosure = $plot->enclosure ?? '';
        $this->planting_pattern = $plot->planting_pattern ?? '';
        $this->slope = $plot->slope ?? '';
        $this->number_of_vines = $plot->number_of_vines ?? '';
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'tenure_regime' => 'nullable|string|in:propiedad,arrendamiento,aparceria,cesion_uso,otros',
            'code_parcel' => 'nullable|string|max:50',
            'orientation' => 'nullable|string|in:N,NE,E,SE,S,SO,O,NO',
            'maximum_yield_kg_ha' => 'nullable|numeric|min:0',
            'degree_day_base' => 'nullable|numeric|min:0|max:30',
            'limit_kg' => 'nullable|numeric|min:0',
            'cadastral_area' => 'nullable|numeric|min:0',
            'unit_of_measurement' => 'nullable|string|in:kg,t',
            'plantation_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'is_organic' => 'boolean',
            'soil_type_id' => 'nullable|exists:soil_types,id',
            'irrigation_type_id' => 'nullable|exists:irrigation_types,id',
            'topography_id' => 'nullable|exists:topographies,id',
            'property_type_id' => 'nullable|exists:property_types,id',
            'valley_id' => 'nullable|exists:valleys,id',
            'site_id' => 'nullable|exists:sites,id',
            'training_system_id' => 'nullable|exists:training_systems,id',
            'owner_id' => 'nullable|exists:users,id',
            'enclosure' => 'nullable|string|max:100',
            'planting_pattern' => 'nullable|string|max:50',
            'slope' => 'nullable|numeric|min:0|max:100',
            'number_of_vines' => 'nullable|integer|min:0',
        ];

        // Viticultor es requerido si el usuario tiene rol que puede seleccionar viticultores
        if (in_array(Auth::user()->role, ['admin', 'supervisor', 'winery', 'viticulturist'])) {
            $rules['viticulturist_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
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
                'tenure_regime' => $this->tenure_regime,
                'code_parcel' => $this->code_parcel ?: null,
                'orientation' => $this->orientation ?: null,
                'maximum_yield_kg_ha' => $this->maximum_yield_kg_ha ?: null,
                'degree_day_base' => $this->degree_day_base ?: null,
                'limit_kg' => $this->limit_kg ?: null,
                'cadastral_area' => $this->cadastral_area ?: null,
                'unit_of_measurement' => $this->unit_of_measurement ?: null,
                'plantation_year' => $this->plantation_year ?: null,
                'is_organic' => $this->is_organic,
                'soil_type_id' => $this->soil_type_id ?: null,
                'irrigation_type_id' => $this->irrigation_type_id ?: null,
                'topography_id' => $this->topography_id ?: null,
                'property_type_id' => $this->property_type_id ?: null,
                'valley_id' => $this->valley_id ?: null,
                'site_id' => $this->site_id ?: null,
                'training_system_id' => $this->training_system_id ?: null,
                'owner_id' => $this->owner_id ?: null,
                'enclosure' => $this->enclosure ?: null,
                'planting_pattern' => $this->planting_pattern ?: null,
                'slope' => $this->slope ?: null,
                'number_of_vines' => $this->number_of_vines ?: null,
            ];

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

            DB::commit();

            $this->toastSuccess('Parcela actualizada correctamente.');
            $indexRoute = Auth::user()->isWinery() ? 'winery.plots.index' : 'plots.index';
            return $this->redirect(route($indexRoute), navigate: true);
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
            'soilTypes' => SoilType::where('active', true)->orderBy('name')->get(),
            'irrigationTypes' => IrrigationType::where('active', true)->orderBy('name')->get(),
            'topographies' => Topography::where('active', true)->orderBy('name')->get(),
            'propertyTypes' => PropertyType::where('active', true)->orderBy('name')->get(),
            'valleys' => Valley::where('active', true)->orderBy('name')->get(),
            'sites' => Site::where('is_archived', false)->orderBy('name')->get(),
            'trainingSystems' => TrainingSystem::where('active', true)->orderBy('name')->get(),
            'autonomousCommunities' => AutonomousCommunity::select(['id', 'name'])
                ->orderBy('name')
                ->get(),
            'provinces' => $this->autonomous_community_id 
                ? Province::select(['id', 'name', 'autonomous_community_id'])
                    ->where('autonomous_community_id', $this->autonomous_community_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'municipalities' => $this->province_id
                ? Municipality::select(['id', 'name', 'province_id'])
                    ->where('province_id', $this->province_id)
                    ->orderBy('name')
                    ->get()
                : collect(),
        ])->layout('layouts.app');
    }
}
