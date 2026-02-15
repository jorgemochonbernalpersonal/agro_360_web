<?php

namespace App\Events;

use App\Models\Plot;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlotLocked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Plot $plot,
        public string $reason
    ) {}
}
