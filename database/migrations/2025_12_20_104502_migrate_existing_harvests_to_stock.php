<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\ContainerCurrentState;
use App\Models\InvoiceItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrar harvests y facturas existentes al sistema de stock
     */
    public function up(): void
    {
        echo "Iniciando migración de datos existentes...\n";

        // PASO 1: Migrar todas las cosechas existentes
        $this->migrateHarvests();

        // PASO 2: Procesar facturas para marcar como vendido/reservado
        $this->migrateInvoices();

        echo "Migración completada.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpiar registros migrados
        DB::table('harvest_stocks')->where('notes', 'LIKE', '%Migración automática%')->delete();
        // Nota: container_states ya fue eliminada, los datos están en container_current_states
        DB::table('container_current_states')->whereNotNull('harvest_id')->delete();
    }

    /**
     * Migrar cosechas existentes
     */
    protected function migrateHarvests(): void
    {
        $harvests = Harvest::with('activity', 'container')->get();
        $total = $harvests->count();
        
        echo "Migrando {$total} cosechas...\n";

        foreach ($harvests as $index => $harvest) {
            try {
                // Crear registro inicial de stock
                HarvestStock::create([
                    'harvest_id' => $harvest->id,
                    'container_id' => $harvest->container_id,
                    'user_id' => $harvest->activity->user_id ?? null,
                    'movement_type' => 'initial',
                    'quantity_change' => $harvest->total_weight,
                    'quantity_after' => $harvest->total_weight,
                    'available_qty' => $harvest->total_weight,
                    'reserved_qty' => 0,
                    'sold_qty' => 0,
                    'gifted_qty' => 0,
                    'lost_qty' => 0,
                    'notes' => 'Migración automática - Registro inicial',
                    'created_at' => $harvest->created_at,
                    'updated_at' => $harvest->updated_at,
                ]);

                // Crear/actualizar estado del contenedor
                if ($harvest->container_id) {
                    $existingState = ContainerCurrentState::where('container_id', $harvest->container_id)->first();

                    if ($existingState) {
                        // Si ya existe (por otra cosecha en el mismo contenedor), sumar
                        $existingState->update([
                            'current_quantity' => $existingState->current_quantity + $harvest->total_weight,
                            'available_qty' => $existingState->available_qty + $harvest->total_weight,
                            'last_movement_at' => now(),
                        ]);
                    } else {
                        // Crear nuevo estado
                        ContainerCurrentState::create([
                            'container_id' => $harvest->container_id,
                            'harvest_id' => $harvest->id,
                            'current_quantity' => $harvest->total_weight,
                            'available_qty' => $harvest->total_weight,
                            'reserved_qty' => 0,
                            'sold_qty' => 0,
                            'has_subproducts' => false,
                            'location' => $harvest->container->location ?? null,
                            'last_movement_at' => now(),
                        ]);
                    }
                }

                if (($index + 1) % 10 == 0) {
                    echo "  Procesadas " . ($index + 1) . "/{$total} cosechas...\n";
                }
            } catch (\Exception $e) {
                echo "  ERROR en harvest {$harvest->id}: " . $e->getMessage() . "\n";
            }
        }

        echo "✓ {$total} cosechas migradas.\n\n";
    }

    /**
     * Migrar facturas existentes
     */
    protected function migrateInvoices(): void
    {
        // Usar DB::table() directamente para evitar problemas con SoftDeletes si la columna no existe aún
        $invoiceItemIds = DB::table('invoice_items')
            ->whereNotNull('harvest_id')
            ->pluck('id');
        
        $invoiceItems = InvoiceItem::withoutGlobalScopes()
            ->whereIn('id', $invoiceItemIds)
            ->with(['harvest.stockMovements', 'invoice'])
            ->get();

        $total = $invoiceItems->count();
        
        echo "Migrando {$total} items de factura...\n";

        foreach ($invoiceItems as $index => $item) {
            try {
                if (!$item->harvest) {
                    continue;
                }

                $lastStock = $item->harvest->stockMovements()->latest()->first();
                
                if (!$lastStock) {
                    echo "  SKIP: No hay stock inicial para harvest {$item->harvest_id}\n";
                    continue;
                }

                $invoiceStatus = $item->invoice->status ?? 'draft';

                // Determinar estado: draft = reservado, otros = vendido
                if ($invoiceStatus === 'draft') {
                    $this->createReservation($item, $lastStock);
                } else {
                    $this->createSale($item, $lastStock);
                }

                if (($index + 1) % 10 == 0) {
                    echo "  Procesados " . ($index + 1) . "/{$total} items...\n";
                }
            } catch (\Exception $e) {
                echo "  ERROR en invoice item {$item->id}: " . $e->getMessage() . "\n";
            }
        }

        echo "✓ {$total} items de factura migrados.\n";
    }

    /**
     * Crear reserva
     */
    protected function createReservation(InvoiceItem $item, $lastStock): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => $item->invoice->user_id,
            'invoice_item_id' => $item->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => max(0, $lastStock->available_qty - $item->quantity),
            'reserved_qty' => $lastStock->reserved_qty + $item->quantity,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => 'Migración automática - Reserva de factura #' . ($item->invoice->invoice_number ?? 'DRAFT'),
            'reference_number' => $item->invoice->invoice_number,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);

        // Actualizar contenedor
        if ($item->harvest->container_id) {
            $state = ContainerCurrentState::where('container_id', $item->harvest->container_id)->first();
            if ($state) {
                $state->update([
                    'available_qty' => max(0, $state->available_qty - $item->quantity),
                    'reserved_qty' => $state->reserved_qty + $item->quantity,
                ]);
            }
        }
    }

    /**
     * Crear venta
     */
    protected function createSale(InvoiceItem $item, $lastStock): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => $item->invoice->user_id,
            'invoice_item_id' => $item->id,
            'movement_type' => 'sale',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => max(0, $lastStock->available_qty - $item->quantity),
            'reserved_qty' => $lastStock->reserved_qty,
            'sold_qty' => $lastStock->sold_qty + $item->quantity,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => 'Migración automática - Venta de factura #' . $item->invoice->invoice_number,
            'reference_number' => $item->invoice->invoice_number,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);

        // Actualizar contenedor
        if ($item->harvest->container_id) {
            $state = ContainerCurrentState::where('container_id', $item->harvest->container_id)->first();
            if ($state) {
                $state->update([
                    'available_qty' => max(0, $state->available_qty - $item->quantity),
                    'sold_qty' => $state->sold_qty + $item->quantity,
                ]);
            }
        }
    }
};
