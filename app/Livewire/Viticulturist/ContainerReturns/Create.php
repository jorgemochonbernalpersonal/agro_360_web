<?php

namespace App\Livewire\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use App\Models\PhytosanitaryProduct;

class Create extends AbstractCreate
{
    public string $campaign_id               = '';
    public string $phytosanitary_product_id  = '';
    public string $date                      = '';
    public string $product_name              = '';
    public string $registration_number       = '';
    public string $container_type            = 'plastic';
    public string $container_size_liters     = '';
    public string $containers_quantity       = '';
    public string $total_weight_kg           = '';
    public string $collection_system         = 'sigfito';
    public string $collection_point          = '';
    public string $transport_document        = '';
    public string $notes                     = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id = (string) ($campaign?->id ?? '');
        $this->date        = now()->format('Y-m-d');
    }

    public function updatedPhytosanitaryProductId(string $v): void
    {
        if ($v) {
            $product = PhytosanitaryProduct::find($v);
            if ($product) {
                $this->product_name          = $product->name;
                $this->registration_number   = $product->registration_number ?? '';
            }
        }
    }

    protected function rules(): array
    {
        return [
            'campaign_id'              => $this->campaignOwnershipRule(),
            'date'                     => 'required|date',
            'product_name'             => 'required|string|max:255',
            'registration_number'      => 'nullable|string|max:50',
            'phytosanitary_product_id' => 'nullable|exists:phytosanitary_products,id',
            'container_type'           => 'required|in:' . implode(',', array_keys(PhytosanitaryContainerReturn::CONTAINER_TYPES)),
            'container_size_liters'    => 'nullable|numeric|min:0.001',
            'containers_quantity'      => 'required|integer|min:1',
            'total_weight_kg'          => 'nullable|numeric|min:0.001',
            'collection_system'        => 'required|in:' . implode(',', array_keys(PhytosanitaryContainerReturn::COLLECTION_SYSTEMS)),
            'collection_point'         => 'required|string|max:255',
            'transport_document'       => 'nullable|string|max:100',
            'notes'                    => 'nullable|string',
        ];
    }

    protected function performCreate(): void
    {
        PhytosanitaryContainerReturn::create([
            'viticulturist_id'         => $this->viticulturistId(),
            'campaign_id'              => $this->campaign_id,
            'phytosanitary_product_id' => $this->phytosanitary_product_id ?: null,
            'date'                     => $this->date,
            'product_name'             => $this->product_name,
            'registration_number'      => $this->registration_number ?: null,
            'container_type'           => $this->container_type,
            'container_size_liters'    => $this->container_size_liters ?: null,
            'containers_quantity'      => $this->containers_quantity,
            'total_weight_kg'          => $this->total_weight_kg ?: null,
            'collection_system'        => $this->collection_system,
            'collection_point'         => $this->collection_point,
            'transport_document'       => $this->transport_document ?: null,
            'notes'                    => $this->notes ?: null,
            'active'                   => true,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Entrega de envases registrada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.container-returns.index';
    }

    protected function viewData(): array
    {
        $userId = $this->viticulturistId();
        return [
            'campaigns'         => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'products'          => PhytosanitaryProduct::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'containerTypes'    => PhytosanitaryContainerReturn::CONTAINER_TYPES,
            'collectionSystems' => PhytosanitaryContainerReturn::COLLECTION_SYSTEMS,
        ];
    }
}
