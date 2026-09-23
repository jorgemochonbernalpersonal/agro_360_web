<?php

namespace App\Livewire\Tools;

use App\Services\Tools\SprayMixCalculator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SprayCalculator extends Component
{
    public const MAX_PRODUCTS = 5;

    public ?float $hectares = 1;

    public ?float $litresPerHectare = 400;

    public ?float $tankLitres = 1000;

    /** @var array<int, array{name: string, dose: float|string|null, mode: string}> */
    public array $products = [
        ['name' => '', 'dose' => null, 'mode' => 'per_ha'],
    ];

    public function updated(string $property): void
    {
        // Validación en vivo solo del campo tocado: no gritar sobre filas aún vacías.
        $this->validateOnly($property);
    }

    public function addProduct(): void
    {
        if (count($this->products) < self::MAX_PRODUCTS) {
            $this->products[] = ['name' => '', 'dose' => null, 'mode' => 'per_ha'];
        }
    }

    public function removeProduct(int $index): void
    {
        if (count($this->products) > 1) {
            unset($this->products[$index]);
            $this->products = array_values($this->products);
            $this->resetValidation();
        }
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function result(): ?array
    {
        $data = [
            'hectares' => $this->hectares,
            'litresPerHectare' => $this->litresPerHectare,
            'tankLitres' => $this->tankLitres,
            'products' => $this->products,
        ];

        if (Validator::make($data, $this->rules())->fails()) {
            return null;
        }

        return app(SprayMixCalculator::class)->calculate(
            (float) $this->hectares,
            (float) $this->litresPerHectare,
            (float) $this->tankLitres,
            $this->products,
        );
    }

    public function render()
    {
        return view('livewire.tools.spray-calculator', [
            'modes' => SprayMixCalculator::MODES,
        ]);
    }

    protected function rules(): array
    {
        return [
            'hectares' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'litresPerHectare' => ['required', 'numeric', 'gt:0', 'max:5000'],
            'tankLitres' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'products' => ['array', 'min:1', 'max:'.self::MAX_PRODUCTS],
            'products.*.name' => ['nullable', 'string', 'max:80'],
            'products.*.dose' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'products.*.mode' => ['required', 'in:'.implode(',', array_keys(SprayMixCalculator::MODES))],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'hectares' => 'superficie',
            'litresPerHectare' => 'volumen de caldo',
            'tankLitres' => 'capacidad de la cuba',
            'products.*.dose' => 'dosis',
            'products.*.mode' => 'unidad de dosis',
        ];
    }
}
