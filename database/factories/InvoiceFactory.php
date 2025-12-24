<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'FAC-' . $this->faker->unique()->numberBetween(1000, 9999),
            'invoice_date' => now(),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_base' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'observations' => null,
            'observations_invoice' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'payment_status' => 'unpaid',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'payment_status' => 'unpaid',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'payment_status' => 'paid',
            'payment_date' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
