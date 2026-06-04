<?php

namespace App\Livewire\Admin\Catalogs;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\ContainerType;
use App\Models\GrapeVariety;
use App\Models\MachineryType;
use App\Models\Pest;
use Livewire\Component;

class Index extends Component
{
    use WithReadOnlyGuard, WithToastNotifications;

    // Locales soportados en el panel
    public const LOCALES = ['es', 'en', 'ca', 'eu', 'gl'];

    public string $activeTab = 'pest';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $editingModel = '';

    // Campos traducibles por modelo y locale (es / en / ca)
    public array $translations = [];

    public function openEdit(int $id, string $modelKey): void
    {
        $catalog = $this->catalogs()[$modelKey] ?? null;
        if (! $catalog) {
            return;
        }

        $record = ($catalog['model'])::findOrFail($id);

        $this->editingId = $id;
        $this->editingModel = $modelKey;
        $this->translations = [];

        foreach (array_keys($catalog['fields']) as $field) {
            foreach (self::LOCALES as $locale) {
                $this->translations[$field][$locale] = $record->getTranslation($field, $locale, false) ?? '';
            }
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $catalog = $this->catalogs()[$this->editingModel] ?? null;
        if (! $catalog || ! $this->editingId) {
            return;
        }

        $record = ($catalog['model'])::findOrFail($this->editingId);

        foreach (array_keys($catalog['fields']) as $field) {
            foreach (self::LOCALES as $locale) {
                $value = trim($this->translations[$field][$locale] ?? '');
                if ($value !== '') {
                    $record->setTranslation($field, $locale, $value);
                }
            }
        }

        $record->save();

        $this->showModal = false;
        $this->toastSuccess(__('Traducciones guardadas.'));
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $catalogs = $this->catalogs();
        $catalog = $catalogs[$this->activeTab];
        $records = ($catalog['model'])::all();

        return view('livewire.admin.catalogs.index', [
            'catalogs' => $catalogs,
            'catalog' => $catalog,
            'records' => $records,
            'locales' => self::LOCALES,
        ])->layout('layouts.app', [
            'title' => __('Catálogos — Admin — Agro365'),
            'description' => __('Gestiona las traducciones de los catálogos del sistema.'),
        ]);
    }

    // Definición de cada catálogo: modelo, label, campos traducibles
    private function catalogs(): array
    {
        return [
            'pest' => [
                'model' => Pest::class,
                'label' => __('Plagas y enfermedades'),
                'fields' => [
                    'name' => __('Nombre'),
                    'description' => __('Descripción'),
                    'symptoms' => __('Síntomas'),
                    'lifecycle' => __('Ciclo de vida'),
                    'prevention_methods' => __('Métodos de prevención'),
                ],
            ],
            'grape_variety' => [
                'model' => GrapeVariety::class,
                'label' => __('Variedades de uva'),
                'fields' => [
                    'name' => __('Nombre'),
                    'description' => __('Descripción'),
                ],
            ],
            'machinery_type' => [
                'model' => MachineryType::class,
                'label' => __('Tipos de maquinaria'),
                'fields' => [
                    'name' => __('Nombre'),
                ],
            ],
            'container_type' => [
                'model' => ContainerType::class,
                'label' => __('Tipos de contenedor'),
                'fields' => [
                    'name' => __('Nombre'),
                    'description' => __('Descripción'),
                ],
            ],
        ];
    }
}
