<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficialReport>
 */
class OfficialReportFactory extends Factory
{
    public function definition(): array
    {
        $periodStart = fake()->dateTimeBetween('-2 years', '-1 month');
        $periodEnd = fake()->dateTimeBetween($periodStart, 'now');

        return [
            'user_id' => User::factory(),
            'report_type' => fake()->randomElement(['phytosanitary_treatments', 'full_digital_notebook']),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'signature_hash' => hash('sha256', Str::random(64)),
            'signed_at' => now(),
            'signed_ip' => fake()->ipv4(),
            'verification_code' => Str::random(32),
            'is_valid' => true,
            'processing_status' => 'completed',
        ];
    }
}
