<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Facturas de venta de productos (vino embotellado).
 * Usa direct DB para evitar InvoiceItemObserver y conflictos de stock.
 */
class WineryInvoicesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    public function run(): void
    {
        $this->cleanup();

        $clientIds = DB::table('clients')
            ->where('user_id', self::WINERY_USER_ID)
            ->pluck('id')
            ->toArray();

        if (empty($clientIds)) {
            $this->command->warn('  ⚠️  Sin clientes. Saltando facturas de venta.');
            return;
        }

        $now      = now();
        $invoices = [
            [
                'client_idx'     => 0,
                'invoice_number' => 'FAC-2025-001',
                'invoice_date'   => now()->subDays(120)->toDateString(),
                'status'         => 'paid',
                'payment_status' => 'paid',
                'payment_type'   => 'transfer',
                'subtotal'       => 1200.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Tinto Joven 2024 — 750ml', 'qty' => 120, 'unit_price' => 7.50, 'unit' => 'botella'],
                    ['name' => 'Rosado Agaete 2024 — 750ml', 'qty' => 48, 'unit_price' => 8.00, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 1,
                'invoice_number' => 'FAC-2025-002',
                'invoice_date'   => now()->subDays(95)->toDateString(),
                'status'         => 'paid',
                'payment_status' => 'paid',
                'payment_type'   => 'transfer',
                'subtotal'       => 2160.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Crianza 2022 — 750ml', 'qty' => 96, 'unit_price' => 13.50, 'unit' => 'botella'],
                    ['name' => 'Blanco Vijariego 2024 — 750ml', 'qty' => 72, 'unit_price' => 10.00, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 2,
                'invoice_number' => 'FAC-2025-003',
                'invoice_date'   => now()->subDays(75)->toDateString(),
                'status'         => 'sent',
                'payment_status' => 'unpaid',
                'payment_type'   => 'transfer',
                'subtotal'       => 840.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Espumoso Brut Nature Gran Agaete — 750ml', 'qty' => 48, 'unit_price' => 17.50, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 3,
                'invoice_number' => 'FAC-2025-004',
                'invoice_date'   => now()->subDays(60)->toDateString(),
                'status'         => 'paid',
                'payment_status' => 'paid',
                'payment_type'   => 'cash',
                'subtotal'       => 540.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Tinto Barrica 2023 — 750ml', 'qty' => 36, 'unit_price' => 11.00, 'unit' => 'botella'],
                    ['name' => 'Malvasía Dulce 2024 — 500ml', 'qty' => 24, 'unit_price' => 8.25, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 4,
                'invoice_number' => 'FAC-2025-005',
                'invoice_date'   => now()->subDays(45)->toDateString(),
                'status'         => 'sent',
                'payment_status' => 'partial',
                'payment_type'   => 'transfer',
                'subtotal'       => 3500.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Tinto Joven 2024 — 750ml (caja 6u)', 'qty' => 60, 'unit_price' => 45.00, 'unit' => 'caja'],
                    ['name' => 'Blanco Barrica Listán 2023 — 750ml', 'qty' => 24, 'unit_price' => 12.50, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 5,
                'invoice_number' => 'FAC-2025-006',
                'invoice_date'   => now()->subDays(30)->toDateString(),
                'status'         => 'draft',
                'payment_status' => 'unpaid',
                'payment_type'   => null,
                'subtotal'       => 720.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Reserva 2021 — 750ml', 'qty' => 36, 'unit_price' => 20.00, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 0,
                'invoice_number' => 'FAC-2025-007',
                'invoice_date'   => now()->subDays(15)->toDateString(),
                'status'         => 'sent',
                'payment_status' => 'unpaid',
                'payment_type'   => 'transfer',
                'subtotal'       => 1680.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Ecológico Tinto 2024 — 750ml', 'qty' => 120, 'unit_price' => 9.00, 'unit' => 'botella'],
                    ['name' => 'Ecológico Blanco 2024 — 750ml', 'qty' => 60, 'unit_price' => 9.00, 'unit' => 'botella'],
                ],
            ],
            [
                'client_idx'     => 1,
                'invoice_number' => 'FAC-2025-008',
                'invoice_date'   => now()->subDays(5)->toDateString(),
                'status'         => 'draft',
                'payment_status' => 'unpaid',
                'payment_type'   => null,
                'subtotal'       => 2520.000,
                'tax_rate'       => 21.0,
                'items' => [
                    ['name' => 'Espumoso Rosé 2024 — 750ml', 'qty' => 72, 'unit_price' => 19.00, 'unit' => 'botella'],
                    ['name' => 'Maceración Carbónica 2024 — 750ml', 'qty' => 48, 'unit_price' => 12.50, 'unit' => 'botella'],
                ],
            ],
        ];

        $totalInvoices = 0;
        $totalItems    = 0;

        foreach ($invoices as $inv) {
            $clientId  = $clientIds[$inv['client_idx'] % count($clientIds)];
            $taxAmount = round($inv['subtotal'] * $inv['tax_rate'] / 100, 3);
            $total     = round($inv['subtotal'] + $taxAmount, 3);

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'         => self::WINERY_USER_ID,
                'client_id'       => $clientId,
                'invoice_number'  => $inv['invoice_number'],
                'invoice_date'    => $inv['invoice_date'],
                'invoice_type'    => 'wine_sale',
                'status'          => $inv['status'],
                'payment_status'  => $inv['payment_status'],
                'payment_type'    => $inv['payment_type'],
                'subtotal'        => $inv['subtotal'],
                'tax_base'        => $inv['subtotal'],
                'tax_rate'        => $inv['tax_rate'],
                'tax_amount'      => $taxAmount,
                'total_amount'    => $total,
                'delivery_status' => $inv['status'] === 'paid' ? 'delivered' : 'pending',
                'sif_status'      => 'pendiente',
                'corrective'      => false,
                'gift'            => false,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $totalInvoices++;

            foreach ($inv['items'] as $item) {
                $itemSubtotal = round($item['qty'] * $item['unit_price'], 3);
                $itemTax      = round($itemSubtotal * $inv['tax_rate'] / 100, 3);

                DB::table('invoice_items')->insert([
                    'invoice_id'      => $invoiceId,
                    'name'            => $item['name'],
                    'concept_type'    => 'product',
                    'quantity'        => $item['qty'],
                    'unit'            => $item['unit'],
                    'unit_price'      => $item['unit_price'],
                    'discount_percentage' => 0,
                    'discount_amount'  => 0,
                    'tax_rate'        => $inv['tax_rate'],
                    'tax_base'        => $itemSubtotal,
                    'tax_amount'      => $itemTax,
                    'subtotal'        => $itemSubtotal,
                    'total'           => round($itemSubtotal + $itemTax, 3),
                    'status'          => 'active',
                    'payment_status'  => $inv['payment_status'] === 'paid' ? 'paid' : 'unpaid',
                    'delivery_status' => $inv['status'] === 'paid' ? 'delivered' : 'pending',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                $totalItems++;
            }
        }

        $this->command->info("✅ Facturas de venta: {$totalInvoices} facturas, {$totalItems} líneas");
    }

    private function cleanup(): void
    {
        $invoiceIds = DB::table('invoices')
            ->where('user_id', self::WINERY_USER_ID)
            ->where('invoice_type', 'wine_sale')
            ->pluck('id');

        if ($invoiceIds->isNotEmpty()) {
            DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
            DB::table('invoices')->whereIn('id', $invoiceIds)->delete();
        }
    }
}
