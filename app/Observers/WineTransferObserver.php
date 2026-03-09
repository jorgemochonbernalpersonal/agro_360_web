<?php

namespace App\Observers;

use App\Models\ContainerHistory;
use App\Models\WineTransfer;
use Illuminate\Support\Facades\Auth;

class WineTransferObserver
{
    public function created(WineTransfer $transfer): void
    {
        $now = now();

        // Decrementar contenedor origen
        if ($transfer->from_container_id && $transfer->fromContainer) {
            $transfer->fromContainer->decrementUsedCapacity((float) $transfer->quantity);

            ContainerHistory::create([
                'container_id'        => $transfer->from_container_id,
                'wine_id'             => $transfer->wine_id,
                'operation_type'      => 'transfer',
                'quantity'            => -(float) $transfer->quantity,
                'start_date'          => $now,
                'created_by'          => Auth::id(),
            ]);
        }

        // Incrementar contenedor destino
        if ($transfer->toContainer) {
            $transfer->toContainer->incrementUsedCapacity((float) $transfer->quantity);

            ContainerHistory::create([
                'container_id'        => $transfer->to_container_id,
                'wine_id'             => $transfer->wine_id,
                'operation_type'      => 'fill',
                'quantity'            => (float) $transfer->quantity,
                'start_date'          => $now,
                'created_by'          => Auth::id(),
            ]);
        }
    }

    public function deleted(WineTransfer $transfer): void
    {
        // Revertir capacidades al eliminar el trasvase
        if ($transfer->from_container_id && $transfer->fromContainer) {
            $transfer->fromContainer->incrementUsedCapacity((float) $transfer->quantity);
        }

        if ($transfer->toContainer) {
            $transfer->toContainer->decrementUsedCapacity((float) $transfer->quantity);
        }
    }
}
