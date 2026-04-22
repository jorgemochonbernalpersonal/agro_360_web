<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Genera 300 facturas de venta de vino embotellado.
 *   · 150 facturas de 2025 (histórico)
 *   · 150 facturas de 2026 (campaña activa — prioridad demo)
 *
 * Usa direct DB para evitar InvoiceItemObserver y conflictos de stock.
 */
class WineryInvoicesSeeder extends Seeder
{
    private const WINERY_USER_ID = 1;

    // Productos 2025 con precio unitario
    private const PRODUCTS_2025 = [
        ['Tinto Joven 2024 — 750ml',         7.50],
        ['Tinto Crianza 2023 — 750ml',       12.00],
        ['Gran Tinto Reserva 2022 — 750ml',  18.00],
        ['Blanco Vijariego 2024 — 750ml',    10.00],
        ['Blanco Barrica 2023 — 750ml',      11.50],
        ['Malvasía Seco 2024 — 750ml',        9.50],
        ['Rosado Agaete 2024 — 750ml',        8.00],
        ['Rosado Joven 2024 — 750ml',         8.50],
        ['Espumoso Brut 2022 — 750ml',       16.00],
        ['Espumoso Rosé 2023 — 750ml',       17.50],
        ['Malvasía Dulce 2023 — 500ml',      14.00],
        ['Tinto Ecológico 2024 — 750ml',     10.00],
        ['Blanco Ecológico 2024 — 750ml',    10.50],
        ['Maceración Carbónica 2024 — 750ml', 9.00],
        ['Tinto Barrica 2023 — 750ml',       11.00],
    ];

    // Productos 2026 con precio unitario
    private const PRODUCTS_2026 = [
        ['Tinto Joven 2026 — 750ml',          8.00],
        ['Tinto Barrica 2026 — 750ml',        13.00],
        ['Tinto Crianza 2025 — 750ml',        12.50],
        ['Blanco Joven 2026 — 750ml',          9.00],
        ['Blanco Barrica 2025 — 750ml',       11.50],
        ['Malvasía Seco 2026 — 750ml',         9.50],
        ['Rosado 2026 — 750ml',                9.50],
        ['Rosado Ecológico 2026 — 750ml',     10.00],
        ['Espumoso Brut 2026 — 750ml',        17.00],
        ['Brut Nature 2026 — 750ml',          19.00],
        ['Malvasía Dulce 2025 — 500ml',       15.00],
        ['Tinto Ecológico 2026 — 750ml',      10.50],
        ['Blanco Ecológico 2026 — 750ml',     11.00],
        ['Gran Tinto Reserva 2023 — 750ml',   20.00],
        ['Vijariego 2026 — 750ml',            12.00],
    ];

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

        $now           = now();
        $totalInvoices = 0;
        $totalItems    = 0;
        $invoiceRows   = [];
        $itemRows      = [];

        // Hoy: 2026-04-22
        // 2025: daysAgo 487 (2025-01-01) → 112 (2025-12-31)
        // 2026: daysAgo 111 (2026-01-01) → 0   (2026-04-22)

        for ($i = 0; $i < 300; $i++) {
            $is2026   = $i >= 150;
            $yearIdx  = $is2026 ? ($i - 150) : $i;
            $year     = $is2026 ? 2026 : 2025;

            // Fecha distribuida linealmente dentro del año
            if ($is2026) {
                $daysAgo     = (int)round(111 - $yearIdx * 111 / 149);
                $invoiceNum  = 'FAC-2026-' . str_pad($yearIdx + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $daysAgo     = (int)round(487 - $yearIdx * 375 / 149);
                $invoiceNum  = 'FAC-2025-' . str_pad($yearIdx + 1, 3, '0', STR_PAD_LEFT);
            }

            $invoiceDate  = now()->subDays($daysAgo)->toDateString();
            $clientId     = $clientIds[$i % count($clientIds)];
            $taxRate      = 21.0;
            $paymentTypes = ['transfer', 'transfer', 'cash', 'transfer', 'other'];
            $paymentType  = $paymentTypes[$i % count($paymentTypes)];

            // Status: 2025 → más pagadas; 2026 → más recientes (draft/sent)
            if ($is2026) {
                $status = match ($i % 6) {
                    0, 1    => 'draft',
                    2, 3    => 'sent',
                    default => 'paid',
                };
            } else {
                $status = match ($i % 5) {
                    0       => 'sent',
                    1       => 'paid',
                    2, 3, 4 => 'paid',
                };
            }

            $paymentStatus = match ($status) {
                'paid'  => 'paid',
                'sent'  => ($i % 3 === 0 ? 'partial' : 'unpaid'),
                default => 'unpaid',
            };

            // Items: 1–3 líneas por factura
            $products    = $is2026 ? self::PRODUCTS_2026 : self::PRODUCTS_2025;
            $numItems    = 1 + ($i % 3);
            $subtotal    = 0.000;
            $pendingItems = [];

            for ($j = 0; $j < $numItems; $j++) {
                [$prodName, $unitPrice] = $products[($i + $j * 5) % count($products)];
                $qty         = 24 + (($i + $j) % 8) * 12;  // 24, 36, 48 ... 108 botellas
                $itemSubtotal= round($qty * $unitPrice, 3);
                $itemTax     = round($itemSubtotal * $taxRate / 100, 3);
                $subtotal   += $itemSubtotal;

                $pendingItems[] = [
                    'name'                => $prodName,
                    'concept_type'        => 'product',
                    'quantity'            => $qty,
                    'unit'                => 'botella',
                    'unit_price'          => $unitPrice,
                    'discount_percentage' => 0,
                    'discount_amount'     => 0,
                    'tax_rate'            => $taxRate,
                    'tax_base'            => $itemSubtotal,
                    'tax_amount'          => $itemTax,
                    'subtotal'            => $itemSubtotal,
                    'total'               => round($itemSubtotal + $itemTax, 3),
                    'status'              => 'active',
                    'payment_status'      => $paymentStatus === 'paid' ? 'paid' : 'unpaid',
                    'delivery_status'     => $status === 'paid' ? 'delivered' : 'pending',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }

            $taxAmount = round($subtotal * $taxRate / 100, 3);
            $total     = round($subtotal + $taxAmount, 3);

            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'         => self::WINERY_USER_ID,
                'client_id'       => $clientId,
                'invoice_number'  => $invoiceNum,
                'invoice_date'    => $invoiceDate,
                'invoice_type'    => 'wine_sale',
                'status'          => $status,
                'payment_status'  => $paymentStatus,
                'payment_type'    => $status === 'draft' ? null : $paymentType,
                'subtotal'        => round($subtotal, 3),
                'tax_base'        => round($subtotal, 3),
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $total,
                'delivery_status' => $status === 'paid' ? 'delivered' : 'pending',
                'sif_status'      => 'pendiente',
                'corrective'      => false,
                'gift'            => false,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            foreach ($pendingItems as $item) {
                $item['invoice_id'] = $invoiceId;
                $itemRows[]         = $item;
                $totalItems++;
            }

            $totalInvoices++;

            // Insert items in batches every 50 invoices
            if (count($itemRows) >= 150) {
                DB::table('invoice_items')->insert($itemRows);
                $itemRows = [];
            }
        }

        if (!empty($itemRows)) {
            DB::table('invoice_items')->insert($itemRows);
        }

        $this->command->info("✅ Facturas de venta: {$totalInvoices} facturas (150 × 2025 + 150 × 2026), {$totalItems} líneas");
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
