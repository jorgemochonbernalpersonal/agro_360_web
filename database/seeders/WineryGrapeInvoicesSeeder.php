<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Liquidaciones / facturas de compra de uva a viticultores.
 * Usa invoice_type = 'grape_purchase' en tabla invoices.
 */
class WineryGrapeInvoicesSeeder extends Seeder
{
    private const WINERY_USER_ID  = 1;
    private const INVOICE_TYPE    = 'grape_purchase';

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

        $now      = now();
        $invoices = [
            [
                'vitic_idx'      => 0,
                'invoice_number' => 'LIQ-2025-001',
                'invoice_date'   => now()->subDays(55)->toDateString(),
                'status'         => 'paid',
                'payment_status' => 'paid',
                'payment_type'   => 'transfer',
                'subtotal'       => 2880.000,
                'tax_rate'       => 0.0,
                'items' => [
                    ['name' => 'Uva Listán Negro — 3200 kg × 0.90€', 'qty' => 3200, 'unit_price' => 0.90, 'unit' => 'kg'],
                ],
                'bank_name'    => 'CaixaBank',
                'bank_account' => 'ES76 2100 0001 0000 0000 0001',
                'observations' => 'Liquidación vendimia 2025 — primera entrega.',
            ],
            [
                'vitic_idx'      => 0,
                'invoice_number' => 'LIQ-2025-002',
                'invoice_date'   => now()->subDays(50)->toDateString(),
                'status'         => 'paid',
                'payment_status' => 'paid',
                'payment_type'   => 'transfer',
                'subtotal'       => 2520.000,
                'tax_rate'       => 0.0,
                'items' => [
                    ['name' => 'Uva Listán Negro — 2800 kg × 0.90€', 'qty' => 2800, 'unit_price' => 0.90, 'unit' => 'kg'],
                ],
                'bank_name'    => 'CaixaBank',
                'bank_account' => 'ES76 2100 0001 0000 0000 0001',
                'observations' => 'Liquidación vendimia 2025 — segunda entrega.',
            ],
            [
                'vitic_idx'      => 1,
                'invoice_number' => 'LIQ-2025-003',
                'invoice_date'   => now()->subDays(45)->toDateString(),
                'status'         => 'sent',
                'payment_status' => 'unpaid',
                'payment_type'   => 'transfer',
                'subtotal'       => 1650.000,
                'tax_rate'       => 0.0,
                'items' => [
                    ['name' => 'Uva Negramoll — 1500 kg × 1.10€', 'qty' => 1500, 'unit_price' => 1.10, 'unit' => 'kg'],
                ],
                'bank_name'    => 'BBVA',
                'bank_account' => 'ES91 0182 0001 0000 0000 0002',
                'observations' => 'Liquidación Negramoll — pendiente confirmación bancaria.',
            ],
            [
                'vitic_idx'      => 1,
                'invoice_number' => 'LIQ-2025-004',
                'invoice_date'   => now()->subDays(40)->toDateString(),
                'status'         => 'sent',
                'payment_status' => 'unpaid',
                'payment_type'   => 'transfer',
                'subtotal'       => 1140.000,
                'tax_rate'       => 0.0,
                'items' => [
                    ['name' => 'Uva Listán Blanco — 1200 kg × 0.95€', 'qty' => 1200, 'unit_price' => 0.95, 'unit' => 'kg'],
                ],
                'bank_name'    => 'BBVA',
                'bank_account' => 'ES91 0182 0001 0000 0000 0002',
                'observations' => null,
            ],
            [
                'vitic_idx'      => count($viticulturistIds) > 2 ? 2 : 0,
                'invoice_number' => 'LIQ-2025-005',
                'invoice_date'   => now()->subDays(35)->toDateString(),
                'status'         => 'draft',
                'payment_status' => 'unpaid',
                'payment_type'   => null,
                'subtotal'       => 1260.000,
                'tax_rate'       => 0.0,
                'items' => [
                    ['name' => 'Uva Vijariego — 900 kg × 1.40€', 'qty' => 900, 'unit_price' => 1.40, 'unit' => 'kg'],
                ],
                'bank_name'    => null,
                'bank_account' => null,
                'observations' => 'Borrador pendiente de validación precio final.',
            ],
        ];

        $totalInvoices = 0;
        $totalItems    = 0;

        foreach ($invoices as $inv) {
            $vitId     = $viticulturistIds[$inv['vitic_idx'] % count($viticulturistIds)];
            $taxAmount = round($inv['subtotal'] * $inv['tax_rate'] / 100, 3);
            $total     = round($inv['subtotal'] + $taxAmount, 3);

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'              => self::WINERY_USER_ID,
                'invoice_type'         => self::INVOICE_TYPE,
                'viticulturist_id'     => $vitId,
                'invoice_number'       => $inv['invoice_number'],
                'invoice_date'         => $inv['invoice_date'],
                'status'               => $inv['status'],
                'payment_status'       => $inv['payment_status'],
                'payment_type'         => $inv['payment_type'],
                'subtotal'             => $inv['subtotal'],
                'tax_base'             => $inv['subtotal'],
                'tax_rate'             => $inv['tax_rate'],
                'tax_amount'           => $taxAmount,
                'total_amount'         => $total,
                'delivery_status'      => 'delivered',
                'sif_status'           => 'pendiente',
                'corrective'           => false,
                'gift'                 => false,
                'bank_name'            => $inv['bank_name'],
                'bank_account_number'  => $inv['bank_account'],
                'observations'         => $inv['observations'],
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            $totalInvoices++;

            foreach ($inv['items'] as $item) {
                $itemSubtotal = round($item['qty'] * $item['unit_price'], 3);
                $itemTax      = round($itemSubtotal * $inv['tax_rate'] / 100, 3);

                DB::table('invoice_items')->insert([
                    'invoice_id'          => $invoiceId,
                    'name'                => $item['name'],
                    'concept_type'        => 'harvest',
                    'quantity'            => $item['qty'],
                    'unit'                => $item['unit'],
                    'unit_price'          => $item['unit_price'],
                    'discount_percentage' => 0,
                    'discount_amount'     => 0,
                    'tax_rate'            => $inv['tax_rate'],
                    'tax_base'            => $itemSubtotal,
                    'tax_amount'          => $itemTax,
                    'subtotal'            => $itemSubtotal,
                    'total'               => round($itemSubtotal + $itemTax, 3),
                    'status'              => 'active',
                    'payment_status'      => $inv['payment_status'] === 'paid' ? 'paid' : 'unpaid',
                    'delivery_status'     => 'delivered',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);

                $totalItems++;
            }
        }

        $this->command->info("✅ Liquidaciones uva: {$totalInvoices} facturas, {$totalItems} líneas");
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
