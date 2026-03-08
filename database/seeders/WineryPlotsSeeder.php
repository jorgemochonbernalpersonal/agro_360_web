<?php

namespace Database\Seeders;

use App\Models\GrapeVariety;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\AutonomousCommunity;
use Illuminate\Database\Seeder;

class WineryPlotsSeeder extends Seeder
{
    public function run(): void
    {
        $viticulturistId = 1;

        $community = AutonomousCommunity::first();
        $province   = Province::first();
        $municipality = Municipality::first();
        $variety    = GrapeVariety::first();

        $plotNames = [
            'La Viña del Norte',
            'Parcela Sur',
            'El Majuelo',
            'Finca Las Eras',
            'El Cerro',
            'La Ladera',
            'Viñedo Alto',
            'El Llano',
            'Parcela del Río',
            'La Solana',
        ];

        foreach ($plotNames as $i => $name) {
            $plot = Plot::create([
                'name'                    => $name,
                'viticulturist_id'        => $viticulturistId,
                'autonomous_community_id' => $community->id,
                'province_id'             => $province->id,
                'municipality_id'         => $municipality->id,
                'area'                    => round(0.5 + $i * 0.3 + mt_rand(0, 10) / 10, 2),
                'active'                  => true,
                'code_parcel'             => 'PARC-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'plantation_year'         => 1990 + $i * 2,
                'is_organic'              => $i % 3 === 0,
            ]);

            PlotPlanting::create([
                'plot_id'          => $plot->id,
                'name'             => 'Plantación ' . ($i + 1),
                'grape_variety_id' => $variety->id,
                'area_planted'     => $plot->area,
                'planting_year'    => $plot->plantation_year,
                'harvest_limit_kg' => round($plot->area * 7000),
                'status'           => 'active',
                'active'           => true,
                'irrigated'        => $i % 2 === 0,
            ]);

            $this->command->info("✓ Parcela #{$plot->id}: {$name}");
        }

        $this->command->info('');
        $this->command->info('✅ 10 parcelas + 10 plantaciones creadas para viticultor ID ' . $viticulturistId);
    }
}
