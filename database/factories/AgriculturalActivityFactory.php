<?php

namespace Database\Factories;

use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgriculturalActivity>
 */
class AgriculturalActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plot_id' => Plot::factory(),
            'plot_planting_id' => null, // Por defecto null, se puede asignar con forPlot() o forPlanting()
            'viticulturist_id' => User::factory(),
            'campaign_id' => Campaign::factory(),
            'activity_type' => fake()->randomElement(['phytosanitary', 'fertilization', 'irrigation', 'cultural', 'observation']),
            'activity_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'crew_id' => null,
            'machinery_id' => null,
            'weather_conditions' => fake()->randomElement(['Soleado', 'Nublado', 'Lluvia', 'Viento']),
            'temperature' => fake()->randomFloat(2, 5, 35),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the activity belongs to a specific viticulturist.
     */
    public function forViticulturist(User $viticulturist): static
    {
        return $this->state(fn (array $attributes) => [
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    /**
     * Indicate that the activity belongs to a specific plot.
     */
    public function forPlot(Plot $plot): static
    {
        // Si la parcela tiene plantaciones activas, asignar una automáticamente
        $plotPlantingId = null;
        $activePlanting = PlotPlanting::where('plot_id', $plot->id)
            ->where('status', 'active')
            ->first();
        
        if ($activePlanting) {
            $plotPlantingId = $activePlanting->id;
        }
        
        return $this->state(fn (array $attributes) => [
            'plot_id' => $plot->id,
            'plot_planting_id' => $plotPlantingId,
            'viticulturist_id' => $plot->viticulturist_id,
        ]);
    }
    
    /**
     * Indicate that the activity belongs to a specific planting.
     */
    public function forPlanting(PlotPlanting $planting): static
    {
        return $this->state(fn (array $attributes) => [
            'plot_id' => $planting->plot_id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $planting->plot->viticulturist_id,
        ]);
    }

    /**
     * Indicate that the activity belongs to a specific campaign.
     */
    public function forCampaign(Campaign $campaign): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_id' => $campaign->id,
            'viticulturist_id' => $campaign->viticulturist_id,
        ]);
    }

    /**
     * Create an activity with a phytosanitary treatment.
     * Used in tests to create activities with associated treatments.
     */
    public function withPhytosanitaryTreatment(array $treatmentAttributes = []): static
    {
        return $this->afterCreating(function (AgriculturalActivity $activity) use ($treatmentAttributes) {
            // Asegurar que la actividad es de tipo phytosanitary
            if ($activity->activity_type !== 'phytosanitary') {
                $activity->update(['activity_type' => 'phytosanitary']);
            }

            // Crear producto fitosanitario si no existe
            $product = \App\Models\PhytosanitaryProduct::firstOrCreate(
                ['registration_number' => 'TEST-' . fake()->unique()->numberBetween(1000, 9999)],
                [
                    'name' => fake()->words(2, true),
                    'active_ingredient' => fake()->word(),
                    'manufacturer' => fake()->company(),
                    'type' => fake()->randomElement(['insecticide', 'fungicide', 'herbicide']),
                    'withdrawal_period_days' => fake()->numberBetween(7, 30),
                ]
            );

            // Crear tratamiento fitosanitario
            \App\Models\PhytosanitaryTreatment::create(array_merge([
                'activity_id' => $activity->id,
                'product_id' => $product->id,
                'dose_per_hectare' => fake()->randomFloat(2, 0.5, 5.0),
                'total_dose' => fake()->randomFloat(2, 1.0, 10.0),
                'area_treated' => fake()->randomFloat(2, 0.5, 5.0),
                'application_method' => fake()->randomElement(['pulverización', 'aplicación foliar', 'riego']),
                'target_pest' => fake()->words(2, true),
                'wind_speed' => fake()->randomFloat(2, 0, 20),
                'humidity' => fake()->randomFloat(2, 30, 90),
                'treatment_justification' => fake()->sentence(),
                'applicator_ropo_number' => fake()->numerify('ROPO-####'),
                'reentry_period_days' => fake()->numberBetween(1, 7),
                'spray_volume' => fake()->randomFloat(2, 200, 1000),
            ], $treatmentAttributes));
        });
    }
}
