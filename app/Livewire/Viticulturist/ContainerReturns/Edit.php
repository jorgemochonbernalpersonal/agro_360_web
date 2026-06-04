<?php

namespace App\Livewire\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use App\Models\PhytosanitaryProduct;

class Edit extends AbstractEdit
{
    public PhytosanitaryContainerReturn $containerReturn;

    public string $campaign_id = '';

    public string $phytosanitary_product_id = '';

    public string $date = '';

    public string $product_name = '';

    public string $registration_number = '';

    public string $container_type = 'plastic';

    public string $container_size_liters = '';

    public string $containers_quantity = '';

    public string $total_weight_kg = '';

    public string $collection_system = 'sigfito';

    public string $collection_point = '';

    public string $transport_document = '';

    public string $notes = '';

    public function mount(PhytosanitaryContainerReturn $containerReturn): void
    {
        $this->authorize($containerReturn);
        $this->containerReturn = $containerReturn;
        $this->campaign_id = (string) $containerReturn->campaign_id;
        $this->phytosanitary_product_id = (string) ($containerReturn->phytosanitary_product_id ?? '');
        $this->date = $containerReturn->date->format('Y-m-d');
        $this->product_name = $containerReturn->product_name;
        $this->registration_number = $containerReturn->registration_number ?? '';
        $this->container_type = $containerReturn->container_type;
        $this->container_size_liters = (string) ($containerReturn->container_size_liters ?? '');
        $this->containers_quantity = (string) $containerReturn->containers_quantity;
        $this->total_weight_kg = (string) ($containerReturn->total_weight_kg ?? '');
        $this->collection_system = $containerReturn->collection_system;
        $this->collection_point = $containerReturn->collection_point;
        $this->transport_document = $containerReturn->transport_document ?? '';
        $this->notes = $containerReturn->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => $this->campaignOwnershipRule(),
            'date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:50',
            'phytosanitary_product_id' => 'nullable|exists:phytosanitary_products,id',
            'container_type' => 'required|in:'.implode(',', array_keys(PhytosanitaryContainerReturn::CONTAINER_TYPES)),
            'container_size_liters' => 'nullable|numeric|min:0.001',
            'containers_quantity' => 'required|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0.001',
            'collection_system' => 'required|in:'.implode(',', array_keys(PhytosanitaryContainerReturn::COLLECTION_SYSTEMS)),
            'collection_point' => 'required|string|max:255',
            'transport_document' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    protected function performUpdate(): void
    {
        $this->containerReturn->update([
            'campaign_id' => $this->campaign_id,
            'phytosanitary_product_id' => $this->phytosanitary_product_id ?: null,
            'date' => $this->date,
            'product_name' => $this->product_name,
            'registration_number' => $this->registration_number ?: null,
            'container_type' => $this->container_type,
            'container_size_liters' => $this->container_size_liters ?: null,
            'containers_quantity' => $this->containers_quantity,
            'total_weight_kg' => $this->total_weight_kg ?: null,
            'collection_system' => $this->collection_system,
            'collection_point' => $this->collection_point,
            'transport_document' => $this->transport_document ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Registro actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.container-returns.index';
    }

    protected function viewData(): array
    {
        $userId = $this->viticulturistId();

        return [
            'campaigns' => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'products' => PhytosanitaryProduct::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'containerTypes' => PhytosanitaryContainerReturn::containerTypeOptions(),
            'collectionSystems' => PhytosanitaryContainerReturn::collectionSystemOptions(),
        ];
    }
}
