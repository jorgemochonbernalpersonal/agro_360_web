<?php

namespace App\Events;

use App\Models\OfficialReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfficialReportGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OfficialReport $report
    ) {}
}
