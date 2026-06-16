<?php

namespace App\Livewire\Plots;

use App\Livewire\Concerns\WithPlotFormRules;
use App\Livewire\Concerns\WithRoleBasedFields;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserFilters;
use App\Models\AutonomousCommunity;
use App\Models\IrrigationType;
use App\Models\Municipality;
use App\Models\Orientation;
use App\Models\Plot;
use App\Models\PropertyType;
use App\Models\Province;
use App\Models\Site;
use App\Models\SoilType;
use App\Models\Topography;
use App\Models\Valley;
use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    use WithPlotFormRules, WithRoleBasedFields, WithToastNotifications, WithUserFilters;

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

    public $is_organic = false;

    // Lookup FKs
    public $soil_type_id = '';

    public $irrigation_type_id = '';

    public $topography_id = '';

    public $property_type_id = '';

    public $valley_id = '';

    public $site_id = '';

    public $owner_id = '';

    // Nuevos campos simples
    public $enclosure = '';

    public $planting_pattern = '';

    public $slope = '';

    // PAC
    public $pac_eligible_area = '';

    public $non_eligible_area = '';

    // Note: provinces/municipalities are NOT stored as public properties.
    // They are computed fresh in render() to avoid snapshot/morphdom conflicts.

    public function mount()
    {
        if (! Auth::user()->can('create', Plot::class)) {
            abort(403);
        }

        // Auto-asignar viticultor si es viticulturist
        // Si es viticultor y no puede seleccionar otros viticultores, se auto-asigna
        if (Auth::user()->hasViticulturistAccess()) {
            if (! $this->canSelectViticulturist()) {
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

    public function save()
    {
        // validate() debe estar FUERA del try-catch para que Livewire
        // pueda interceptar ValidationException y emitir el snapshot correcto.
        // Si se atrapa aquí, el pipeline interno de Livewire queda interrumpido
        // y el JS lanza "Snapshot missing on Livewire component".
        $this->validate();

        // La validación de permisos debe estar FUERA del try-catch igual que validate(),
        // para que Livewire pueda interceptar la ValidationException correctamente.
        // Si se atrapa dentro del catch(\Exception), el pipeline de Livewire queda
        // interrumpido y el JS lanza "Snapshot missing on Livewire component".
        $user = Auth::user();
        $viticulturistForPlot = null;

        if ($this->canSelectViticulturist() && $this->viticulturist_id) {
            $canAssign = false;

            if ($user->isProducer()) {
                $canAssign = (int) $this->viticulturist_id === $user->id
                    || \App\Models\WineryViticulturist::where('viticulturist_id', $this->viticulturist_id)
                        ->where('winery_id', $user->id)
                        ->where('source', \App\Models\WineryViticulturist::SOURCE_OWN)
                        ->where('assigned_by', $user->id)
                        ->exists()
                    || $user->canEditViticulturist($this->viticulturist_id);
            } elseif ($user->hasWineryAccess()) {
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

            if (! $canAssign) {
                throw ValidationException::withMessages([
                    'viticulturist_id' => __('Solo puedes asignar parcelas a viticultores que has creado.'),
                ]);
            }

            $viticulturistForPlot = $this->viticulturist_id;
        } elseif ($user->hasViticulturistAccess()) {
            $viticulturistForPlot = $user->id;
        }

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
                'is_organic' => $this->is_organic,
                'soil_type_id' => $this->soil_type_id ?: null,
                'irrigation_type_id' => $this->irrigation_type_id ?: null,
                'topography_id' => $this->topography_id ?: null,
                'property_type_id' => $this->property_type_id ?: null,
                'valley_id' => $this->valley_id ?: null,
                'site_id' => $this->site_id ?: null,
                'owner_id' => $this->owner_id ?: null,
                'enclosure' => $this->enclosure ?: null,
                'planting_pattern' => $this->planting_pattern ?: null,
                'slope' => $this->slope ?: null,
                'pac_eligible_area' => $this->pac_eligible_area ?: null,
                'non_eligible_area' => $this->non_eligible_area ?: null,
            ];

            if ($viticulturistForPlot) {
                $data['viticulturist_id'] = $viticulturistForPlot;
            }

            if ($this->canSelectLocation()) {
                $data['autonomous_community_id'] = $this->autonomous_community_id;
                $data['province_id'] = $this->province_id;
                $data['municipality_id'] = $this->municipality_id;
            }

            $plot = Plot::create($data);

            DB::commit();

            $this->toastSuccess(__('Parcela creada correctamente.'));
            $indexRoute = $user->isProducer() ? 'producer.plots.index' : ($user->hasWineryAccess() ? 'winery.plots.index' : 'plots.index');

            return $this->redirect(route($indexRoute), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar la excepción completa para debugging
            Log::error('Error al crear parcela: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $data ?? [],
                'exception' => $e,
            ]);

            $this->toastError(__('Error inesperado al crear la parcela. Por favor, inténtalo de nuevo.'));

            return;
        }
    }

    #[Renderless]
    public function getMunicipalities(string $provinceId): array
    {
        if (! $provinceId) {
            return [];
        }

        return Municipality::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.plots.create', [
            'orientations' => Orientation::where('active', true)->get(),
            'soilTypes' => $this->catalogScope(SoilType::where('active', true), 'soil_types')->orderBy('name')->get(),
            'irrigationTypes' => $this->catalogScope(IrrigationType::where('active', true), 'irrigation_types')->orderBy('name')->get(),
            'topographies' => $this->catalogScope(Topography::where('active', true), 'topographies')->orderBy('name')->get(),
            'propertyTypes' => $this->catalogScope(PropertyType::where('active', true), 'property_types')->orderBy('name')->get(),
            'valleys' => $this->catalogScope(Valley::where('active', true), 'valleys')->orderBy('name')->get(),
            'sites' => $this->catalogScope(Site::where('is_archived', false), 'sites')->orderBy('name')->get(),
            'autonomousCommunities' => AutonomousCommunity::select(['id', 'name', 'code'])->orderBy('name')->get(),
            'allProvinces' => Province::orderBy('name')->get(['id', 'name', 'autonomous_community_id'])->toArray(),
            'initMunicipalities' => $this->province_id
                ? Municipality::where('province_id', $this->province_id)->orderBy('name')->get(['id', 'name'])->toArray()
                : [],
        ]);
    }

    protected function rules(): array
    {
        return $this->plotFormRules(producerRequiresViticulturist: true);
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

        return $query->where(fn ($q) => $q->whereNull('user_id')->whereNotIn('id', $hidden)->orWhere('user_id', Auth::id()));
    }
}
