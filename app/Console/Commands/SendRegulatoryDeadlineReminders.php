<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\RegulatoryDeadlineNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendRegulatoryDeadlineReminders extends Command
{
    protected $signature   = 'agro:regulatory-reminders {--dry-run : Muestra qué enviaría sin enviar nada}';
    protected $description = 'Envía recordatorios de plazos INFOVI a bodegas y productores';

    private const REMIND_DAYS = [7, 1];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today  = now()->startOfDay();
        $sent   = 0;

        $users = User::whereIn('role', ['winery', 'producer'])
            ->where('can_login', true)
            ->whereNotNull('email')
            ->get(['id', 'name', 'email', 'role']);

        foreach ($users as $user) {
            $isLarge   = $this->isLargeProducer($user->id);
            $deadlines = $this->buildDeadlines($isLarge, $today);

            foreach ($deadlines as $deadline) {
                $deadlineDate = Carbon::parse($deadline['date']);
                $daysLeft     = (int) $today->diffInDays($deadlineDate, false);

                if (!in_array($daysLeft, self::REMIND_DAYS)) {
                    continue;
                }

                // Deduplication: skip if already notified for this exact deadline + days_left
                if ($this->alreadyNotified($user->id, $deadline['date'], $daysLeft)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] {$user->email} — {$deadline['label']} ({$daysLeft}d)");
                    continue;
                }

                $user->notify(new RegulatoryDeadlineNotification(
                    deadlineLabel: $deadline['label'],
                    deadlineDate:  $deadline['date'],
                    daysLeft:      $daysLeft,
                    producerType:  $isLarge ? 'gran' : 'pequeño',
                    deadlineType:  $deadline['type'],
                ));

                $sent++;
                $this->line("✓ Enviado a {$user->email} — {$deadline['label']} ({$daysLeft}d)");
            }
        }

        $this->info("Recordatorios enviados: {$sent}");
        return self::SUCCESS;
    }

    private function isLargeProducer(int $wineryId): bool
    {
        $campaigns = DB::table('wine_stock_snapshots')
            ->where('user_id', $wineryId)
            ->where('is_must', false)
            ->selectRaw('YEAR(snapshot_date) as yr, SUM(quantity_liters) / 100.0 as hl')
            ->groupBy('yr')
            ->orderByDesc('yr')
            ->limit(4)
            ->pluck('hl');

        if ($campaigns->isEmpty()) {
            return false;
        }

        return $campaigns->avg() > 1000;
    }

    private function buildDeadlines(bool $isLarge, Carbon $today): array
    {
        if ($isLarge) {
            $nextMonth = $today->copy()->addMonth()->startOfMonth();
            return [[
                'label' => 'Declaración mensual ' . $nextMonth->translatedFormat('F Y'),
                'date'  => $nextMonth->copy()->setDay(19)->toDateString(),
                'type'  => 'monthly',
            ]];
        }

        $deadlines  = [];
        $candidates = [
            $today->year . '-08-19',
            $today->year . '-12-19',
            ($today->year + 1) . '-08-19',
            ($today->year + 1) . '-12-19',
        ];

        foreach ($candidates as $d) {
            if ($d >= $today->toDateString()) {
                $date  = Carbon::parse($d);
                $month = $date->month === 12 ? 'noviembre' : 'julio';
                $deadlines[] = [
                    'label' => 'Declaración ampliada ' . $month . ' ' . $date->year,
                    'date'  => $d,
                    'type'  => 'semi_annual',
                ];
                if (count($deadlines) >= 2) {
                    break;
                }
            }
        }

        return $deadlines;
    }

    private function alreadyNotified(int $userId, string $deadlineDate, int $daysLeft): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->where('type', RegulatoryDeadlineNotification::class)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.deadline_date')) = ?", [$deadlineDate])
            ->whereRaw("JSON_EXTRACT(data, '$.days_left') = ?", [$daysLeft])
            ->exists();
    }
}
