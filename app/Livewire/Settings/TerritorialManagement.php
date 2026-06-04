<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AutonomousCommunity;
use App\Models\IrrigationType;
use App\Models\Municipality;
use App\Models\PropertyType;
use App\Models\Province;
use App\Models\Site;
use App\Models\SoilType;
use App\Models\Topography;
use App\Models\TrainingSystem;
use App\Models\Valley;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class TerritorialManagement extends Component
{
    use WithToastNotifications;

    #[Url]
    public string $tab = 'sites';

    // --- Parajes ---
    public string $selectedCommunityId = '';

    public string $selectedProvinceId = '';

    public string $selectedMunicipalityId = '';

    public string $newSiteName = '';

    public int $editingSiteId = 0;

    public string $editingSiteName = '';

    // --- Catálogos simples ---
    public string $newName = '';

    public string $newDescription = '';

    public string $newCode = '';

    public string $catalogSearch = '';

    public int $editingId = 0;

    public string $editingName = '';

    public string $editingDescription = '';

    // ── Cascada ubicación ────────────────────────────────────────────────────

    public function updatedSelectedCommunityId(): void
    {
        $this->selectedProvinceId = '';
        $this->selectedMunicipalityId = '';
        $this->editingSiteId = 0;
    }

    public function updatedSelectedProvinceId(): void
    {
        $this->selectedMunicipalityId = '';
        $this->editingSiteId = 0;
    }

    public function updatedSelectedMunicipalityId(): void
    {
        $this->editingSiteId = 0;
    }

    // ── Parajes ───────────────────────────────────────────────────────────────

    public function addSite(): void
    {
        $this->validate([
            'selectedMunicipalityId' => 'required|exists:municipalities,id',
            'newSiteName' => 'required|string|min:2|max:255',
        ], [
            'selectedMunicipalityId.required' => __('Selecciona un municipio primero.'),
            'newSiteName.required' => __('El nombre del paraje es obligatorio.'),
        ]);

        $exists = Site::where('municipality_id', $this->selectedMunicipalityId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($this->newSiteName))])
            ->exists();

        if ($exists) {
            $this->addError('newSiteName', __('Este paraje ya existe en el municipio seleccionado.'));

            return;
        }

        Site::create([
            'user_id' => Auth::id(),
            'name' => trim($this->newSiteName),
            'municipality_id' => $this->selectedMunicipalityId,
            'is_archived' => false,
        ]);

        $this->newSiteName = '';
        $this->toastSuccess(__('Paraje añadido correctamente.'));
    }

    public function deleteSite(int $id): void
    {
        Site::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        $this->toastSuccess(__('Paraje eliminado.'));
    }

    public function toggleSiteVisibility(int $id): void
    {
        $this->toggleGlobalVisibility('sites', $id);
    }

    public function startEditSite(int $id, string $name): void
    {
        $this->editingSiteId = $id;
        $this->editingSiteName = $name;
    }

    public function saveEditSite(): void
    {
        $this->validate(['editingSiteName' => 'required|string|min:2|max:255']);

        $site = Site::where('id', $this->editingSiteId)->where('user_id', Auth::id())->firstOrFail();

        $exists = Site::where('municipality_id', $site->municipality_id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($this->editingSiteName))])
            ->where('id', '!=', $site->id)
            ->exists();

        if ($exists) {
            $this->addError('editingSiteName', __('Ya existe un paraje con ese nombre en este municipio.'));

            return;
        }

        $site->update(['name' => trim($this->editingSiteName)]);
        $this->editingSiteId = 0;
        $this->editingSiteName = '';
        $this->toastSuccess(__('Paraje actualizado.'));
    }

    public function cancelEditSite(): void
    {
        $this->editingSiteId = 0;
        $this->editingSiteName = '';
    }

    // ── Catálogos genéricos ───────────────────────────────────────────────────

    public function addCatalogItem(string $type): void
    {
        $this->validate(['newName' => 'required|string|min:2|max:255']);

        $model = $this->catalogModel($type);
        $data = ['user_id' => Auth::id(), 'name' => trim($this->newName), 'active' => true];

        if ($this->newDescription) {
            $data['description'] = trim($this->newDescription);
        }
        if ($type === 'valleys' && $this->newCode) {
            $data['code'] = trim($this->newCode);
        }

        $model::create($data);

        $this->newName = '';
        $this->newDescription = '';
        $this->newCode = '';
        $this->toastSuccess(__(':item añadido correctamente.', ['item' => $this->catalogLabel($type)]));
    }

    public function deleteCatalogItem(string $type, int $id): void
    {
        $model = $this->catalogModel($type);
        $model::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        $this->toastSuccess(__(':item eliminado correctamente.', ['item' => $this->catalogLabel($type)]));
    }

    /** Activa/desactiva la visibilidad de un item GLOBAL en los selects del usuario */
    public function toggleCatalogVisibility(string $type, int $id): void
    {
        $this->toggleGlobalVisibility($type, $id);
    }

    public function startEdit(int $id, string $name, string $description = ''): void
    {
        $this->editingId = $id;
        $this->editingName = $name;
        $this->editingDescription = $description;
    }

    public function saveEdit(string $type): void
    {
        $this->validate(['editingName' => 'required|string|min:2|max:255']);

        $model = $this->catalogModel($type);
        $data = ['name' => trim($this->editingName)];

        if ($this->editingDescription !== '') {
            $data['description'] = trim($this->editingDescription);
        }

        $model::where('id', $this->editingId)->where('user_id', Auth::id())->firstOrFail()->update($data);

        $this->editingId = 0;
        $this->editingName = '';
        $this->editingDescription = '';
        $this->toastSuccess(__(':item actualizado correctamente.', ['item' => $this->catalogLabel($type)]));
    }

    public function cancelEdit(): void
    {
        $this->editingId = 0;
        $this->editingName = '';
        $this->editingDescription = '';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $communities = collect();
        $provinces = collect();
        $municipalities = collect();
        $sites = collect();
        $hiddenIds = [];

        if ($this->tab === 'sites') {
            $communities = AutonomousCommunity::select(['id', 'name'])->orderBy('name')->get();

            if ($this->selectedCommunityId) {
                $provinces = Province::select(['id', 'name'])
                    ->where('autonomous_community_id', $this->selectedCommunityId)
                    ->orderBy('name')
                    ->get();
            }

            if ($this->selectedProvinceId) {
                $municipalities = Municipality::select(['id', 'name'])
                    ->where('province_id', $this->selectedProvinceId)
                    ->orderBy('name')
                    ->get();
            }

            if ($this->selectedMunicipalityId) {
                $hiddenIds = $this->hiddenIds('sites');
                $sites = Site::where('municipality_id', $this->selectedMunicipalityId)
                    ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', Auth::id()))
                    ->orderBy('name')
                    ->get();
            }
        }

        $catalogItems = collect();
        $catalogMap = [
            'valleys' => Valley::class,
            'soil_types' => SoilType::class,
            'irrigation_types' => IrrigationType::class,
            'topographies' => Topography::class,
            'property_types' => PropertyType::class,
            'training_systems' => TrainingSystem::class,
        ];

        if (isset($catalogMap[$this->tab])) {
            $hiddenIds = $this->hiddenIds($this->tab);
            $model = $catalogMap[$this->tab];
            $catalogItems = $model::where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', Auth::id()))
                ->when($this->catalogSearch, fn ($q) => $q->where('name', 'like', '%'.$this->catalogSearch.'%'))
                ->orderBy('name')
                ->get();
        }

        return view('livewire.settings.territorial-management', [
            'communities' => $communities,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'sites' => $sites,
            'catalogItems' => $catalogItems,
            'hiddenIds' => $hiddenIds,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function catalogModel(string $type): string
    {
        return match ($type) {
            'valleys' => Valley::class,
            'soil_types' => SoilType::class,
            'irrigation_types' => IrrigationType::class,
            'topographies' => Topography::class,
            'property_types' => PropertyType::class,
            'training_systems' => TrainingSystem::class,
            default => abort(404),
        };
    }

    private function catalogLabel(string $type): string
    {
        return match ($type) {
            'valleys' => __('Valle'),
            'soil_types' => __('Tipo de suelo'),
            'irrigation_types' => __('Tipo de riego'),
            'topographies' => __('Topografía'),
            'property_types' => __('Tipo de propiedad'),
            'training_systems' => __('Sistema de conducción'),
            default => __('Elemento'),
        };
    }

    /** IDs de items globales ocultados por este usuario para un tipo dado */
    private function hiddenIds(string $catalogType): array
    {
        return DB::table('user_catalog_hidden')
            ->where('user_id', Auth::id())
            ->where('catalog_type', $catalogType)
            ->pluck('item_id')
            ->all();
    }

    // ── Shared toggle helper ──────────────────────────────────────────────────

    private function toggleGlobalVisibility(string $catalogType, int $itemId): void
    {
        $exists = DB::table('user_catalog_hidden')
            ->where('user_id', Auth::id())
            ->where('catalog_type', $catalogType)
            ->where('item_id', $itemId)
            ->exists();

        if ($exists) {
            DB::table('user_catalog_hidden')
                ->where('user_id', Auth::id())
                ->where('catalog_type', $catalogType)
                ->where('item_id', $itemId)
                ->delete();
            $this->toastSuccess(__('Elemento activado en tus selects.'));
        } else {
            DB::table('user_catalog_hidden')->insert([
                'user_id' => Auth::id(),
                'catalog_type' => $catalogType,
                'item_id' => $itemId,
            ]);
            $this->toastSuccess(__('Elemento ocultado de tus selects.'));
        }
    }
}
