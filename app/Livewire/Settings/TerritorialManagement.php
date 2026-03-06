<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\IrrigationType;
use App\Models\Municipality;
use App\Models\PropertyType;
use App\Models\Site;
use App\Models\SoilType;
use App\Models\Topography;
use App\Models\TrainingSystem;
use App\Models\Valley;
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
    public string $municipalitySearch = '';
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

    // --- Validaciones por tab ---

    public function addSite(): void
    {
        $this->validate([
            'selectedMunicipalityId' => 'required|exists:municipalities,id',
            'newSiteName' => 'required|string|min:2|max:255',
        ], [
            'selectedMunicipalityId.required' => 'Selecciona un municipio primero.',
            'newSiteName.required' => 'El nombre del paraje es obligatorio.',
        ]);

        $exists = Site::where('municipality_id', $this->selectedMunicipalityId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($this->newSiteName))])
            ->exists();

        if ($exists) {
            $this->addError('newSiteName', 'Este paraje ya existe en el municipio seleccionado.');
            return;
        }

        Site::create([
            'name' => trim($this->newSiteName),
            'municipality_id' => $this->selectedMunicipalityId,
            'is_archived' => false,
        ]);

        $this->newSiteName = '';
        $this->toastSuccess('Paraje añadido correctamente.');
    }

    public function deleteSite(int $id): void
    {
        $site = Site::findOrFail($id);

        if ($site->plots()->exists()) {
            $this->toastError('No se puede eliminar: hay parcelas vinculadas a este paraje.');
            return;
        }

        $site->delete();
        $this->toastSuccess('Paraje eliminado.');
    }

    public function startEditSite(int $id, string $name): void
    {
        $this->editingSiteId = $id;
        $this->editingSiteName = $name;
    }

    public function saveEditSite(): void
    {
        $this->validate([
            'editingSiteName' => 'required|string|min:2|max:255',
        ]);

        $site = Site::findOrFail($this->editingSiteId);

        $exists = Site::where('municipality_id', $site->municipality_id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($this->editingSiteName))])
            ->where('id', '!=', $site->id)
            ->exists();

        if ($exists) {
            $this->addError('editingSiteName', 'Ya existe un paraje con ese nombre en este municipio.');
            return;
        }

        $site->update(['name' => trim($this->editingSiteName)]);
        $this->editingSiteId = 0;
        $this->editingSiteName = '';
        $this->toastSuccess('Paraje actualizado.');
    }

    public function cancelEditSite(): void
    {
        $this->editingSiteId = 0;
        $this->editingSiteName = '';
    }

    // --- Catálogos genéricos ---

    private function catalogModel(string $type): string
    {
        return match($type) {
            'valleys'          => Valley::class,
            'soil_types'       => SoilType::class,
            'irrigation_types' => IrrigationType::class,
            'topographies'     => Topography::class,
            'property_types'   => PropertyType::class,
            'training_systems' => TrainingSystem::class,
            default            => abort(404),
        };
    }

    public function addCatalogItem(string $type): void
    {
        $this->validate(['newName' => 'required|string|min:2|max:255']);

        $model = $this->catalogModel($type);
        $data = ['name' => trim($this->newName), 'active' => true];

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
        $this->toastSuccess('Elemento añadido correctamente.');
    }

    public function deleteCatalogItem(string $type, int $id): void
    {
        $model = $this->catalogModel($type);
        $item = $model::findOrFail($id);

        // Comprobar si tiene parcelas vinculadas
        if ($item->plots()->exists()) {
            $this->toastError('No se puede eliminar: hay parcelas usando este elemento.');
            return;
        }

        $item->delete();
        $this->toastSuccess('Elemento eliminado.');
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

        $model::findOrFail($this->editingId)->update($data);

        $this->editingId = 0;
        $this->editingName = '';
        $this->editingDescription = '';
        $this->toastSuccess('Actualizado correctamente.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = 0;
        $this->editingName = '';
        $this->editingDescription = '';
    }

    public function render()
    {
        // Parajes
        $municipalities = collect();
        $sites = collect();

        if ($this->tab === 'sites') {
            $municipalities = Municipality::select(['id', 'name', 'province_id'])
                ->when($this->municipalitySearch, fn($q) => $q->where('name', 'like', '%' . $this->municipalitySearch . '%'))
                ->orderBy('name')
                ->limit(100)
                ->get();

            if ($this->selectedMunicipalityId) {
                $sites = Site::where('municipality_id', $this->selectedMunicipalityId)
                    ->orderBy('name')
                    ->get();
            }
        }

        // Catálogos
        $catalogItems = collect();
        $catalogMap = [
            'valleys'          => Valley::class,
            'soil_types'       => SoilType::class,
            'irrigation_types' => IrrigationType::class,
            'topographies'     => Topography::class,
            'property_types'   => PropertyType::class,
            'training_systems' => TrainingSystem::class,
        ];

        if (isset($catalogMap[$this->tab])) {
            $model = $catalogMap[$this->tab];
            $catalogItems = $model::when(
                $this->catalogSearch,
                fn($q) => $q->where('name', 'like', '%' . $this->catalogSearch . '%')
            )->orderBy('name')->get();
        }

        return view('livewire.settings.territorial-management', [
            'municipalities' => $municipalities,
            'sites'          => $sites,
            'catalogItems'   => $catalogItems,
        ]);
    }
}
