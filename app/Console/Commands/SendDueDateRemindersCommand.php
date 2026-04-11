<?php

namespace App\Console\Commands;

use App\Models\SupervisorRequest;
use App\Notifications\SupervisorRequestDueSoonNotification;
use Illuminate\Console\Command;

class SendDueDateRemindersCommand extends Command
{
    protected $signature   = 'supervisor-requests:due-reminders {--days=* : Días antes del vencimiento (default: 3 y 1)}';
    protected $description = 'Avisa a las bodegas de solicitudes próximas a vencer';

    public function handle(): int
    {
        $daysList = $this->option('days');

        if (empty($daysList)) {
            $daysList = [3, 1];
        }

        $sent = 0;

        foreach ($daysList as $days) {
            $days     = (int) $days;
            $target   = today()->addDays($days);

            $requests = SupervisorRequest::whereDate('due_date', $target)
                ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
                ->with('winery:id,name,email')
                ->get();

            foreach ($requests as $req) {
                if (! $req->winery?->email) {
                    continue;
                }

                $req->winery->notify(new SupervisorRequestDueSoonNotification($req, $days));
                $sent++;

                $this->line("✓ Aviso ({$days}d) → {$req->winery->email} — {$req->typeLabel()}");
            }
        }

        $this->info("Avisos de vencimiento enviados: {$sent}");
        return self::SUCCESS;
    }
}
