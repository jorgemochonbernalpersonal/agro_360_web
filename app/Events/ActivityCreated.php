<?php

namespace App\Events;

use App\Models\AgriculturalActivity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AgriculturalActivity $activity
    ) {}
}
