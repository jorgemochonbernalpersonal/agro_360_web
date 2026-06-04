<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 450 liquidaciones de compra de uva a viticultores.
 *   · 225 liquidaciones de 2025 (histórico)
 *   · 225 liquidaciones de 2026 (campaña activa)
 *
 * Usa invoice_type = 'grape_purchase' en tabla invoices.
 */
class WineryGrapeInvoicesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    private const INVOICE_TYPE = 'grape_purchase';

    private const VARIETIES = [
        ['Listán Negro',        0.90],
        ['Listán Blanco',       0.95],
        ['Negramoll',           1.10],
        ['Malvasía Volcánica',  1.40],
        ['Vijariego',           1.35],
        ['Marmajuelo',          1.20],
        ['Baboso Negro',        1.15],
        ['Gual',                1.25],
        ['Moscatel',            1.30],
        ['Tintilla',            1.05],
    ];

    private const BANKS = [
        ['CaixaBank', 'ES76 2100 0001 00'],
        ['BBVA',      'ES91 0182 0001 00'],
        ['Santander', 'ES80 0049 0001 00'],
        ['Bankinter', 'ES58 0128 0001 00'],
        ['Sabadell',  'ES02 0081 0001 00'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $viticulturistIds = DB::table('winery_viticulturist')
            ->where('winery_id', self::WINERY_USER_ID)
            ->pluck('viticulturist_id')
            ->toArray();

        if (empty($viticulturistIds)) {
            $this->command->warn('  ⚠️  Sin viticultores vinculados. Saltando liquidaciones de uva.');

            return;
        }

        $now = now();
        $itemRows = [];
        $total = 0;

        // Hoy: 2026-04-22
        // 2025: daysAgo 487 (2025-01-01) → 112 (2025-12-31)
        // 2026: daysAgo 111 (2026-01-01) → 0   (2026-04-22)

        for ($i = 0; $i < 450; $i++) {
            $is2026 = $i >= 225;
            $yearIdx = $is2026 ? ($i - 225) : $i;
            $year = $is2026 ? 2026 : 2025;

            if ($is2026) {
                $daysAgo = (int) round(111 - $yearIdx * 111 / 224);
                $invNumber = 'LIQ-2026-'.str_pad($yearIdx + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $daysAgo = (int) round(487 - $yearIdx * 375 / 224);
                $invNumber = 'LIQ-2025-'.str_pad($yearIdx + 1, 3, '0', STR_PAD_LEFT);
            }

            $invDate = now()->subDays($daysAgo)->toDateString();
            $vitId = $viticulturistIds[$i % count($viticulturistIds)];

            // 2025 → mayoría pagadas; 2026 → más recientes (sent/draft)
            if ($is2026) {
                $status = match ($i % 5) {
                    0, 1 => 'draft',
                    2 => 'sent',
                    default => 'paid',
                };
            } else {
                $status = match ($i % 6) {
                    0 => 'sent',
                    default => 'paid',
                };
            }

            $paymentStatus = $status === 'paid' ? 'paid' : 'unpaid';

            [$variety, $pricePerKg] = self::VARIETIES[$i % count(self::VARIETIES)];
            $kg = 800 + ($i % 20) * 150;   // 800–3650 kg
            $subtotal = round($kg * $pricePerKg, 3);
            $taxRate = 0.0;
            $total_ = $subtotal;

            [$bankName, $bankBase] = self::BANKS[$i % count(self::BANKS)];
            $bankAccount = $bankBase.str_pad($i + 1, 12, '0', STR_PAD_LEFT);

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id' => self::WINERY_USER_ID,
                'invoice_type' => self::INVOICE_TYPE,
                'viticulturist_id' => $vitId,
                'invoice_number' => $invNumber,
                'invoice_date' => $invDate,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_type' => $status === 'draft' ? null : 'transfer',
                'subtotal' => $subtotal,
                'tax_base' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => 0.000,
                'total_amount' => $total_,
                'delivery_status' => 'delivered',
                'sif_status' => 'pendiente',
                'corrective' => false,
                'gift' => false,
                'bank_name' => $status !== 'draft' ? $bankName : null,
                'bank_account_number' => $status !== 'draft' ? $bankAccount : null,
                'observations' => "Liquidación $variety — vendimia $year. Entrega Nº ".($i + 1).'.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $itemRows[] = [
                'invoice_id' => $invoiceId,
                'name' => "Uva $variety — {$kg} kg × ".number_format($pricePerKg, 2).'€',
                'concept_type' => 'harvest',
                'quantity' => $kg,
                'unit' => 'kg',
                'unit_price' => $pricePerKg,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'tax_rate' => $taxRate,
                'tax_base' => $subtotal,
                'tax_amount' => 0.000,
                'subtotal' => $subtotal,
                'total' => $total_,
                'status' => 'active',
                'payment_status' => $paymentStatus,
                'delivery_status' => 'delivered',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $total++;

            if (count($itemRows) >= 100) {
                DB::table('invoice_items')->insert($itemRows);
                $itemRows = [];
            }
        }

        if (! empty($itemRows)) {
            DB::table('invoice_items')->insert($itemRows);
        }

        $this->command->info("✅ Liquidaciones uva: {$total} facturas (225 × 2025 + 225 × 2026), {$total} líneas");
    }

    private function cleanup(): void
    {
        $invoiceIds = DB::table('invoices')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->pluck('id');

        if ($invoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
            DB::table('invoices')->whereIn('id', $invoiceIds)->delete();
        }
    }
}
