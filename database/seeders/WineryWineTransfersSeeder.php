<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea traslados de vino entre contenedores.
 * Depende de: WineryWinesSeeder, WineryContainersSeeder
 */
class WineryWineTransfersSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $now = now();

        $unitId = $this->getLitrosUnitId();
        if (! $unitId) {
            $this->command->warn('No se encontró unidad "Litros" en units_of_measurement.');

            return;
        }

        $wines = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->whereNotIn('status', ['sold', 'cancelled'])
            ->get(['id', 'status', 'vintage', 'volume_liters'])
            ->toArray();

        $allContainers = DB::table('containers')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('archived', false)
            ->pluck('id')
            ->toArray();

        if (empty($allContainers) || empty($wines)) {
            $this->command->warn('Faltan vinos o contenedores.');

            return;
        }

        $rows = [];
        $cidx = 0;

        foreach ($wines as $wine) {
            $numTransfers = match ($wine->status) {
                'in_progress' => 2,
                'aged' => 3,
                'bottled' => 1,
                default => 1,
            };

            $types = ['racking', 'racking', 'top_up', 'blending'];

            for ($i = 0; $i < $numTransfers; $i++) {
                $fromId = $allContainers[$cidx % count($allContainers)];
                $cidx++;
                $toId = $allContainers[$cidx % count($allContainers)];
                $cidx++;

                // Evitar from === to
                if ($fromId === $toId) {
                    $toId = $allContainers[($cidx + 1) % count($allContainers)];
                }

                $type = $types[$i % count($types)];
                $daysAgo = ($numTransfers - $i) * 15 + mt_rand(1, 10);
                $quantity = round(mt_rand(800, 5000) + mt_rand(0, 999) / 1000, 3);

                $rows[] = [
                    'wine_id' => $wine->id,
                    'from_container_id' => $fromId,
                    'to_container_id' => $toId,
                    'quantity' => $quantity,
                    'unit_of_measurement_id' => $unitId,
                    'transfer_type' => $type,
                    'transfer_date' => now()->subDays($daysAgo)->format('Y-m-d'),
                    'notes' => $this->noteForType($type, $i),
                    'created_by' => self::WINERY_USER_ID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('wine_transfers')->insert($rows);

        $this->command->info('✅ Traslados de vino: '.count($rows).' registros');
    }

    private function noteForType(string $type, int $i): ?string
    {
        return match ($type) {
            'racking' => $i === 0 ? 'Primer trasiego post-fermentación. Separación de lías gruesas.' : 'Trasiego de limpieza.',
            'top_up' => 'Relleno para compensar evaporación. Merma natural de bodega.',
            'blending' => 'Mezcla de lotes para homogeneizar perfil organoléptico.',
            default => null,
        };
    }

    private function getLitrosUnitId(): ?int
    {
        return DB::table('units_of_measurement')->where('symbol', 'L')->value('id')
            ?? DB::table('units_of_measurement')->first()?->id;
    }

    private function cleanup(): void
    {
        $wineIds = DB::table('wines')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id');

        DB::table('wine_transfers')->whereIn('wine_id', $wineIds)->delete();
    }
}
