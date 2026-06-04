<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $userIds,
        public string $notificationClass,
        public array $notificationParams = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting bulk notification job', [
            'user_count' => count($this->userIds),
            'notification_class' => $this->notificationClass,
        ]);

        $users = User::whereIn('id', $this->userIds)->get();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                $notification = new $this->notificationClass(...$this->notificationParams);
                $user->notify($notification);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send notification to user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        Log::info('Bulk notification job completed', [
            'sent' => $sentCount,
            'failed' => $failedCount,
        ]);
    }
}
