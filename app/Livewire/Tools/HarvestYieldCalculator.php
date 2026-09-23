<?php

namespace App\Livewire\Tools;

use App\Services\Tools\HarvestYieldEstimator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HarvestYieldCalculator extends Component
{
    public ?float $hectares = 1;

    public ?float $rowSpacing = 2.5;

    public ?float $vineSpacing = 1.2;

    public ?float $clustersPerVine = 12;

    public ?float $clusterWeight = 180;

    public ?float $missingPercent = 5;

    public ?float $litresPer100Kg = 70;

    public ?float $limitKgPerHectare = null;

    public function updated(string $property): void
    {
        $this->validateOnly($property);
    }

    /** @return array<string, float|null>|null */
    #[Computed]
    public function result(): ?array
    {
        $data = [
            'hectares' => $this->hectares,
            'rowSpacing' => $this->rowSpacing,
            'vineSpacing' => $this->vineSpacing,
            'clustersPerVine' => $this->clustersPerVine,
            'clusterWeight' => $this->clusterWeight,
            'missingPercent' => $this->missingPercent,
            'litresPer100Kg' => $this->litresPer100Kg,
            'limitKgPerHectare' => $this->limitKgPerHectare,
        ];

        if (Validator::make($data, $this->rules())->fails()) {
            return null;
        }

        return app(HarvestYieldEstimator::class)->estimate(
            (float) $this->hectares,
            (float) $this->rowSpacing,
            (float) $this->vineSpacing,
            (float) $this->clustersPerVine,
            (float) $this->clusterWeight,
            (float) $this->missingPercent,
            (float) $this->litresPer100Kg,
            $this->limitKgPerHectare,
        );
    }

    public function render()
    {
        return view('livewire.tools.harvest-yield-calculator');
    }

    protected function rules(): array
    {
        return [
            'hectares' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'rowSpacing' => ['required', 'numeric', 'gt:0', 'max:20'],
            'vineSpacing' => ['required', 'numeric', 'gt:0', 'max:20'],
            'clustersPerVine' => ['required', 'numeric', 'gte:0', 'max:200'],
            'clusterWeight' => ['required', 'numeric', 'gt:0', 'max:3000'],
            'missingPercent' => ['required', 'numeric', 'gte:0', 'lt:100'],
            'litresPer100Kg' => ['required', 'numeric', 'gt:0', 'max:100'],
            'limitKgPerHectare' => ['nullable', 'numeric', 'gt:0', 'max:100000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'hectares' => 'superficie',
            'rowSpacing' => 'distancia entre filas',
            'vineSpacing' => 'distancia entre cepas',
            'clustersPerVine' => 'racimos por cepa',
            'clusterWeight' => 'peso medio del racimo',
            'missingPercent' => 'marras',
            'litresPer100Kg' => 'rendimiento de transformación',
            'limitKgPerHectare' => 'límite de producción',
        ];
    }
}
