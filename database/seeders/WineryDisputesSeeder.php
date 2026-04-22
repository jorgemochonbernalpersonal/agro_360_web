<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 harvest_deliveries con estados mixed (delivered/disputed/resolved).
 * El DisputeController filtra: whereHas('harvest', fn => winery_id = 1).
 * Depende de: WineryGrapeReceptionsSeeder
 */
class WineryDisputesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const DISPUTE_NOTES = [
        'El viticultor indica más kg de los registrados en báscula. Diferencia sin justificación.',
        'Discrepancia en peso: viticultor declara un exceso respecto a lo recibido en bodega.',
        'Viticultor alega que parte de la carga no fue pesada correctamente.',
        'Diferencia de humedad detectada: uva con mayor porcentaje de agua del declarado.',
        'Discrepancia en variedad: se recibió mezcla no declarada.',
        'Error en ticket de pesaje — viticultor reclama revisión.',
        'Báscula descalibrada durante la jornada. Registro en revisión.',
        'Carga parcial no registrada en el momento de la entrega.',
    ];

    private const RESOLUTION_NOTES = [
        'Calibración de báscula verificada. Se acepta el peso registrado por bodega. Viticultor conforme.',
        'Revisión conjunta de tickets. Se acuerda ajuste de 50 kg a favor del viticultor.',
        'Análisis de humedad realizado. Descuento aplicado por exceso de agua. Liquidación ajustada.',
        'Error administrativo corregido. Liquidación reenviada y aceptada.',
        'Pesaje repetido con báscula certificada. Resultado coincide con registro bodega.',
        'Acuerdo extrajudicial: bodega asume 30 kg de diferencia. Viticultor firma conforme.',
        'Peritaje de báscula: equipo en rango de tolerancia. Reclamación desestimada.',
        'Resolución por arbitraje D.O. Gran Canaria. Se aplica media entre ambas cifras.',
    ];

    public function run(): void
    {
        $this->cleanup();

        $wineryHarvests = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->orderBy('id')
            ->get()
            ->toArray();

        if (empty($wineryHarvests)) {
            $this->command->warn('  ⚠️  Sin recepciones de uva. Saltando deliveries/disputas.');
            return;
        }

        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando disputas.');
            return;
        }

        $now  = now();
        $rows = [];

        // Status pool: mayoría delivered, ~20% disputed, ~15% resolved
        $statusPool = [
            'delivered', 'delivered', 'delivered', 'delivered', 'delivered',
            'disputed', 'disputed',
            'resolved',
        ];

        for ($i = 0; $i < 450; $i++) {
            $harvest   = $wineryHarvests[$i % count($wineryHarvests)];
            $vitId     = $viticulturistIds[$i % count($viticulturistIds)];
            $status    = $statusPool[$i % count($statusPool)];
            $vintage   = (int)($harvest->harvest_start_date ? substr($harvest->harvest_start_date, 0, 4) : 2025);

            $baseKg       = $harvest->total_weight > 0 ? $harvest->total_weight : 1000.0;
            $discPct      = 5.0 + ($i % 8);   // 5%–12% de discrepancia
            $deliveredKg  = round($baseKg * (1 + $discPct / 100), 2);
            $discrepancyKg= round($deliveredKg - $baseKg, 2);
            $pricePerKg   = $harvest->price_per_kg > 0 ? $harvest->price_per_kg : 0.90;

            $daysAgo       = 365 - (int)round($i * 360 / 449);
            $deliveryDate  = $harvest->harvest_start_date ?? now()->subDays($daysAgo)->toDateString();

            $disputeNote    = null;
            $disputeAt      = null;
            $resolutionNote = null;
            $resolvedAt     = null;

            if ($status === 'disputed') {
                $disputeNote = self::DISPUTE_NOTES[$i % count(self::DISPUTE_NOTES)];
                $disputeAt   = now()->subDays(max(1, $daysAgo - 5))->toDateTimeString();
            } elseif ($status === 'resolved') {
                $disputeNote    = self::DISPUTE_NOTES[$i % count(self::DISPUTE_NOTES)];
                $disputeAt      = now()->subDays(max(10, $daysAgo - 5))->toDateTimeString();
                $resolutionNote = self::RESOLUTION_NOTES[$i % count(self::RESOLUTION_NOTES)];
                $resolvedAt     = now()->subDays(max(1, $daysAgo - 10))->toDateTimeString();
            }

            $rows[] = [
                'viticulturist_id'        => $vitId,
                'harvest_id'              => $harvest->id,
                'vintage_year'            => $vintage,
                'buyer_name'              => 'Bodega Agaete Demo',
                'delivered_kg'            => $deliveredKg,
                'price_per_kg'            => $pricePerKg,
                'total_price'             => round($deliveredKg * $pricePerKg, 2),
                'delivery_date'           => $deliveryDate,
                'ticket_number'           => 'TKT-' . $vintage . '-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'status'                  => $status,
                'discrepancy_kg'          => $discrepancyKg,
                'dispute_note'            => $disputeNote,
                'dispute_submitted_at'    => $disputeAt,
                'dispute_resolution_note' => $resolutionNote,
                'dispute_resolved_at'     => $resolvedAt,
                'created_at'              => $now,
                'updated_at'              => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('harvest_deliveries')->insert($chunk);
        }

        $delivered = count(array_filter($rows, fn($r) => $r['status'] === 'delivered'));
        $disputed  = count(array_filter($rows, fn($r) => $r['status'] === 'disputed'));
        $resolved  = count(array_filter($rows, fn($r) => $r['status'] === 'resolved'));
        $this->command->info("✅ Deliveries/Disputas: " . count($rows) . " registros ({$delivered} entregadas, {$disputed} en disputa, {$resolved} resueltas)");
    }

    private function cleanup(): void
    {
        $wineryHarvestIds = DB::table('harvests')
            ->where('winery_id', self::WINERY_USER_ID)
            ->whereNull('activity_id')
            ->pluck('id');

        if ($wineryHarvestIds->isNotEmpty()) {
            DB::table('harvest_deliveries')
                ->whereIn('harvest_id', $wineryHarvestIds)
                ->delete();
        }
    }
}
