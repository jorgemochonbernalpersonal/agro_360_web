<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea análisis fisicoquímicos para los vinos.
 * Depende de: WineryWinesSeeder, WineryContainersSeeder
 */
class WineryWineAnalysisSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotIn('status', ['cancelled'])
            ->get(['id', 'status', 'wine_type', 'vintage'])
            ->toArray();

        $containerIds = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        $labs = [
            'Laboratorio Enológico Atlántico, Las Palmas de GC',
            'Agrolab Canarias S.L.',
            null, // análisis propio
        ];

        $rows = [];
        $cidx = 0;

        foreach ($wines as $wine) {
            $numAnalyses = match ($wine->status) {
                'in_progress' => 3,
                'aged' => 2,
                'bottled' => 2,
                'sold' => 1,
                default => 1,
            };

            for ($i = 0; $i < $numAnalyses; $i++) {
                $isExternal = $i === ($numAnalyses - 1); // el último es externo
                $lab = $isExternal ? $labs[0] : $labs[2];
                $daysAgo = ($numAnalyses - $i) * 25 + mt_rand(0, 10);
                $analysiDate = now()->subDays($daysAgo)->format('Y-m-d');

                // Valores fisicoquímicos realistas según tipo de vino
                $alcohol = match ($wine->wine_type) {
                    'red' => round(13.0 + mt_rand(0, 15) / 10, 2),
                    'white' => round(11.5 + mt_rand(0, 15) / 10, 2),
                    'rose' => round(12.0 + mt_rand(0, 10) / 10, 2),
                    'sparkling' => round(11.5 + mt_rand(0, 10) / 10, 2),
                    'sweet' => round(14.0 + mt_rand(0, 20) / 10, 2),
                    default => round(12.5 + mt_rand(0, 10) / 10, 2),
                };

                $ph = round(3.20 + mt_rand(0, 30) / 100, 2);
                $totalAcidity = round(5.5 + mt_rand(-10, 15) / 10, 2);
                $volatileAcid = round(0.20 + mt_rand(0, 25) / 100, 2);
                $residualSugar = $wine->wine_type === 'sweet'
                    ? round(100 + mt_rand(0, 50), 2)
                    : round(mt_rand(1, 4) + mt_rand(0, 9) / 10, 2);
                $so2Free = round(mt_rand(15, 35) + mt_rand(0, 9) / 10, 1);
                $so2Total = round($so2Free + mt_rand(20, 60), 1);
                $density = round(0.990 + mt_rand(0, 20) / 1000, 4);

                $result = ($volatileAcid < 0.60 && $so2Total < 150) ? 'passed' : 'pending';

                $rows[] = [
                    'user_id' => self::WINERY_USER_ID,
                    'wine_id' => $wine->id,
                    'container_id' => $containerIds[$cidx % count($containerIds)],
                    'analysis_date' => $analysiDate,
                    'analysis_type' => $isExternal ? 'complete' : 'standard',
                    'laboratory_name' => $lab,
                    'laboratory' => $lab,
                    'sample_reference' => $isExternal ? 'REF-'.$wine->id.'-'.($i + 1).'-'.$wine->vintage : null,
                    'alcoholic_strength' => $alcohol,
                    'alcohol' => $alcohol,
                    'residual_sugar' => $residualSugar,
                    'total_acidity' => $totalAcidity,
                    'volatile_acidity' => $volatileAcid,
                    'ph' => $ph,
                    'free_so2' => $so2Free,
                    'total_so2' => $so2Total,
                    'so2_free' => $so2Free,
                    'so2_total' => $so2Total,
                    'density' => $density,
                    'turbidity' => round(mt_rand(1, 15) / 10, 2),
                    'color_intensity' => $wine->wine_type === 'red' ? round(0.4 + mt_rand(0, 8) / 10, 3) : null,
                    'malic_acid' => round(mt_rand(1, 20) / 10, 2),
                    'document' => null,
                    'result' => $result,
                    'notes' => $i === 0 ? 'Análisis de inicio de elaboración.' : null,
                    'created_by' => self::WINERY_USER_ID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $cidx++;
            }
        }

        DB::table('wine_analyses')->insert($rows);

        $this->command->info('✅ Análisis de vino: '.count($rows).' registros');
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('wine_analyses')->whereIn('wine_id', $wineIds)->delete();
    }
}
