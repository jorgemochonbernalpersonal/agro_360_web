<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyDigestNotification;
use Illuminate\Console\Command;

class SendDailyDigestCommand extends Command
{
    protected $signature = 'notifications:send-digest';

    protected $description = 'Send a daily digest email to users who prefer digest delivery';

    public function handle(): int
    {
        $this->info('Sending daily digest emails...');

        $users = User::whereNotNull('notification_preferences')
            ->get()
            ->filter(function (User $user) {
                $prefs = $user->notification_preferences;

                return ($prefs['delivery'] ?? 'instant') === 'digest';
            });

        $sentCount = 0;

        foreach ($users as $user) {
            $unread = $user->unreadNotifications()
                ->where('created_at', '>=', now()->subDay())
                ->get();

            if ($unread->isEmpty()) {
                continue;
            }

            $user->notify(new DailyDigestNotification($unread));
            $sentCount++;

            $this->line("Digest sent to {$user->name} ({$unread->count()} notifications)");
        }

        $this->info("Done. Sent {$sentCount} digest emails.");

        return self::SUCCESS;
    }
}
