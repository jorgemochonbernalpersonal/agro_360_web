<?php

namespace App\Observers;

use App\Models\ContainerHistory;
use App\Models\WineLoss;
use Illuminate\Support\Facades\Auth;

class WineLossObserver
{
    public function created(WineLoss $loss): void
    {
        // Decrementar contenedor si se especificó
        if ($loss->container_id && $loss->container) {
            $loss->container->decrementUsedCapacity((float) $loss->quantity);

            ContainerHistory::create([
                'container_id'   => $loss->container_id,
                'wine_id'        => $loss->wine_id,
                'operation_type' => 'adjustment',
                'quantity'       => -(float) $loss->quantity,
                'start_date'     => now(),
                'created_by'     => Auth::id(),
            ]);
        }

        // Decrementar volumen del vino
        $wine = $loss->wine;
        if ($wine) {
            $wine->volume_liters = max(0, (float) $wine->volume_liters - (float) $loss->quantity);
            $wine->saveQuietly();
        }
    }

    public function deleted(WineLoss $loss): void
    {
        // Revertir capacidad del contenedor
        if ($loss->container_id && $loss->container) {
            $loss->container->incrementUsedCapacity((float) $loss->quantity);
        }

        // Revertir volumen del vino
        $wine = $loss->wine;
        if ($wine) {
            $wine->volume_liters = (float) $wine->volume_liters + (float) $loss->quantity;
            $wine->saveQuietly();
        }
    }
}
