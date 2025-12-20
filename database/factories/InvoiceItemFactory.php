<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Harvest;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 10, 1000);
        $unitPrice = $this->faker->randomFloat(2, 0.5, 5);
        $subtotal = $quantity * $unitPrice;
        
        return [
            'invoice_id' => Invoice::factory(),
            'harvest_id' => null, // Optional - will be set when needed
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_id' => null,
            'tax_name' => null,
            'tax_rate' => 0,
            'tax_base' => $subtotal,
            'tax_amount' => 0,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'concept_type' => 'other',
        ];
    }

    public function withHarvest(?Harvest $harvest = null): static
    {
        return $this->state(function (array $attributes) use ($harvest) {
            $harvestModel = $harvest ?? Harvest::factory()->create();
            
            return [
                'harvest_id' => $harvestModel->id,
                'name' => 'Cosecha ' . $harvestModel->plotPlanting->grapeVariety->name ?? 'Uva',
                'concept_type' => 'harvest',
                'quantity' => min($attributes['quantity'], $harvestModel->total_weight / 2), // No más de la mitad
            ];
        });
    }

    public function withTax(?Tax $tax = null): static
    {
        return $this->state(function (array $attributes) use ($tax) {
            $taxModel = $tax ?? Tax::where('rate', 21)->first() ?? Tax::factory()->create(['rate' => 21]);
            $taxAmount = $attributes['subtotal'] * ($taxModel->rate / 100);
            
            return [
                'tax_id' => $taxModel->id,
                'tax_name' => $taxModel->name,
                'tax_rate' => $taxModel->rate,
                'tax_amount' => $taxAmount,
                'total' => $attributes['subtotal'] + $taxAmount,
            ];
        });
    }
}
