<?php

namespace App\Observers;

use App\Models\PhytosanitaryTreatment;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Log;

class PhytosanitaryTreatmentObserver
{
    /**
     * Handle the PhytosanitaryTreatment "created" event.
     */
    public function created(PhytosanitaryTreatment $treatment): void
    {
        $this->consumeStock($treatment);
    }

    /**
     * Handle the PhytosanitaryTreatment "updated" event.
     */
    public function updated(PhytosanitaryTreatment $treatment): void
    {
        // Si cambió la dosis total, ajustar el stock
        if ($treatment->wasChanged('total_dose')) {
            $oldDose = $treatment->getOriginal('total_dose') ?? 0;
            $newDose = $treatment->total_dose ?? 0;
            $difference = $newDose - $oldDose;

            if ($difference != 0) {
                $this->adjustStockForTreatment($treatment, $difference);
            }
        }
    }

    /**
     * Descontar stock al crear tratamiento
     */
    protected function consumeStock(PhytosanitaryTreatment $treatment): void
    {
        if (!$treatment->total_dose || !$treatment->product_id) {
            return;
        }

        $user = $treatment->activity->viticulturist ?? $treatment->activity->user;
        if (!$user) {
            return;
        }

        $quantityNeeded = (float) $treatment->total_dose;
        $remaining = $quantityNeeded;

        // Buscar stocks disponibles (FIFO: primero los que caducan antes)
        $availableStocks = ProductStock::availableForProduct($treatment->product_id, $user->id)
            ->get();

        foreach ($availableStocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $available = $stock->getAvailableQuantity();
            if ($available <= 0) {
                continue;
            }

            $toConsume = min($remaining, $available);
            $plotName = $treatment->activity->plot->name ?? 'N/A';
            $stock->consume($toConsume, $treatment, 
                "Tratamiento en parcela: {$plotName}");

            $remaining -= $toConsume;
        }

        // Si no hay suficiente stock, registrar advertencia
        if ($remaining > 0) {
            Log::warning('Stock insuficiente para tratamiento', [
                'treatment_id' => $treatment->id,
                'product_id' => $treatment->product_id,
                'quantity_needed' => $quantityNeeded,
                'quantity_available' => $quantityNeeded - $remaining,
                'shortage' => $remaining,
                'user_id' => $user->id,
            ]);

            // Opcional: crear notificación para el usuario
            // $user->notify(new LowStockNotification($treatment->product, $remaining));
        }
    }

    /**
     * Ajustar stock cuando se modifica un tratamiento
     */
    protected function adjustStockForTreatment(PhytosanitaryTreatment $treatment, float $difference): void
    {
        $user = $treatment->activity->viticulturist ?? $treatment->activity->user;
        if (!$user) {
            return;
        }

        if ($difference > 0) {
            // Se aumentó la dosis, descontar más
            $this->consumeStock($treatment);
        } else {
            // Se disminuyó la dosis, devolver stock (opcional, más complejo)
            // Por ahora solo logueamos
            Log::info('Dosis reducida en tratamiento', [
                'treatment_id' => $treatment->id,
                'difference' => abs($difference),
                'user_id' => $user->id,
            ]);
        }
    }
}

