<?php

namespace App\Livewire\Plots;

use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithUserFilters;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\ViticulturistSetting;
use App\Models\Orientation;
use App\Models\SoilType;
use Illuminate\Support\Facades\DB;
use App\Models\IrrigationType;
use App\Models\Topography;
use App\Models\PropertyType;
use App\Models\Valley;
use App\Models\Site;
use App\Models\TrainingSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Create extends Component
{
    use WithRoleBasedFields, WithUserFilters, WithToastNotifications;

    public $name = '';
    public $description = '';
    public $viticulturist_id = '';
    public $area = '';
    public $active = true;
    public $autonomous_community_id = '';
    public $province_id = '';
    public $municipality_id = '';
    public $code_parcel = '';
    public $orientation_id = '';
    public $degree_day_base = '';
    public $cadastral_area = '';
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
    // PAC
    public $pac_eligible_area = '';
    public $non_eligible_area = '';

    public function mount()
    {
        if (!Auth::user()->can('create', Plot::class)) {
            abort(403);
        }

        // Auto-asignar viticultor si es viticulturist
        // Si es viticultor y no puede seleccionar otros viticultores, se auto-asigna
        if (Auth::user()->hasViticulturistAccess()) {
            if (!$this->canSelectViticulturist()) {
                $this->viticulturist_id = Auth::id();
            }

            // Pre-rellenar parámetros agronómicos desde configuración del viticultor
            $settings = ViticulturistSetting::forUser(Auth::id());
            if ($settings?->degree_day_base) {
                $this->degree_day_base = $settings->degree_day_base;
            }
        }

        // Si bodega navega desde el perfil de un viticultor, pre-seleccionar ese viticultor
        if (Auth::user()->hasWineryAccess() && request()->filled('viticulturist_id')) {
            $this->viticulturist_id = request()->query('viticulturist_id');
        }
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'required|numeric|min:0.001',
            'active' => 'boolean',
            'code_parcel' => 'nullable|string|max:50',
            'orientation_id' => 'nullable|exists:orientations,id',
            'degree_day_base' => 'nullable|numeric|min:0|max:30',
            'cadastral_area' => 'nullable|numeric|min:0',
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
            'pac_eligible_area' => 'nullable|numeric|min:0|lte:area',
            'non_eligible_area' => 'nullable|numeric|min:0|lte:area',
        ];

        // Viticultor es requerido si el usuario tiene rol que puede seleccionar viticultores
        if (in_array(Auth::user()->role, ['admin', 'supervisor', 'winery', 'viticulturist', 'producer'])) {
            $rules['viticulturist_id'] = 'required|exists:users,id';
        }

        if ($this->canSelectLocation()) {
            $rules['autonomous_community_id'] = 'required|exists:autonomous_communities,id';
            $rules['province_id'] = 'required|exists:provinces,id';
            $rules['municipality_id'] = 'required|exists:municipalities,id';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'pac_eligible_area.lte' => 'La superficie admisible PAC no puede superar la superficie total de la parcela.',
            'non_eligible_area.lte' => 'La superficie no admisible no puede superar la superficie total de la parcela.',
        ];
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
                'code_parcel' => $this->code_parcel ?: null,
                'orientation_id' => $this->orientation_id ?: null,
                'degree_day_base' => $this->degree_day_base ?: null,
                'cadastral_area' => $this->cadastral_area ?: null,
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
                'pac_eligible_area' => $this->pac_eligible_area ?: null,
                'non_eligible_area' => $this->non_eligible_area ?: null,
            ];

            if ($this->canSelectViticulturist() && $this->viticulturist_id) {
                $user = Auth::user();
                $canAssign = false;

                if ($user->hasWineryAccess()) {
                    $canAssign = \App\Models\WineryViticulturist::where('viticulturist_id', $this->viticulturist_id)
                        ->where('winery_id', $user->id)
                        ->where('source', \App\Models\WineryViticulturist::SOURCE_OWN)
                        ->where('assigned_by', $user->id)
                        ->exists();
                } elseif ($user->hasViticulturistAccess()) {
                    $canAssign = $user->canEditViticulturist($this->viticulturist_id);
                } else {
                    $canAssign = true;  // Admin y supervisor
                }

                if (!$canAssign) {
                    throw ValidationException::withMessages([
                        'viticulturist_id' => 'Solo puedes asignar parcelas a viticultores que has creado.',
                    ]);
                }

                $data['viticulturist_id'] = $this->viticulturist_id;
            } elseif (Auth::user()->hasViticulturistAccess()) {
                // Auto-asignar viticultor si es viticulturist y no puede seleccionar o no seleccionó ninguno
                $data['viticulturist_id'] = Auth::id();
            }

            if ($this->canSelectLocation()) {
                $data['autonomous_community_id'] = $this->autonomous_community_id;
                $data['province_id'] = $this->province_id;
                $data['municipality_id'] = $this->municipality_id;
            }

            $plot = Plot::create($data);

            DB::commit();

            $this->toastSuccess('Parcela creada correctamente.');
            $indexRoute = Auth::user()->hasWineryAccess() ? 'winery.plots.index' : 'plots.index';
            return $this->redirect(route($indexRoute), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar la excepción completa para debugging
            Log::error('Error al crear parcela: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $data ?? [],
                'exception' => $e
            ]);

            $this->toastError('Error inesperado al crear la parcela. Por favor, inténtalo de nuevo.');

            return;
        }
    }

    private function hiddenIds(string $catalogType): array
    {
        return DB::table('user_catalog_hidden')
            ->where('user_id', Auth::id())
            ->where('catalog_type', $catalogType)
            ->pluck('item_id')
            ->all();
    }

    private function catalogScope($query, string $catalogType)
    {
        $hidden = $this->hiddenIds($catalogType);
        return $query->where(fn($q) => $q->whereNull('user_id')->whereNotIn('id', $hidden)->orWhere('user_id', Auth::id()));
    }

    #[Renderless]
    public function fetchProvinces(int $communityId): array
    {
        $this->province_id = '';
        $this->municipality_id = '';
        return Province::where('autonomous_community_id', $communityId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    #[Renderless]
    public function fetchMunicipalities(int $provinceId): array
    {
        $this->province_id = $provinceId;
        $this->municipality_id = '';
        return Municipality::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    #[Renderless]
    public function selectMunicipality(int $municipalityId): void
    {
        $this->municipality_id = $municipalityId;
    }

    public function render()
    {
        return view('livewire.plots.create', [
            'orientations'   => Orientation::where('active', true)->get(),
            'soilTypes'      => $this->catalogScope(SoilType::where('active', true), 'soil_types')->orderBy('name')->get(),
            'irrigationTypes'=> $this->catalogScope(IrrigationType::where('active', true), 'irrigation_types')->orderBy('name')->get(),
            'topographies'   => $this->catalogScope(Topography::where('active', true), 'topographies')->orderBy('name')->get(),
            'propertyTypes'  => $this->catalogScope(PropertyType::where('active', true), 'property_types')->orderBy('name')->get(),
            'valleys'        => $this->catalogScope(Valley::where('active', true), 'valleys')->orderBy('name')->get(),
            'sites'          => $this->catalogScope(Site::where('is_archived', false), 'sites')->orderBy('name')->get(),
            'trainingSystems'=> $this->catalogScope(TrainingSystem::where('active', true), 'training_systems')->orderBy('name')->get(),
            'autonomousCommunities' => AutonomousCommunity::select(['id', 'name', 'code'])
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
