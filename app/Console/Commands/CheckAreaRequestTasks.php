<?php

namespace App\Console\Commands;

use App\Models\PlotRemoteSensing;
use App\Services\RemoteSensing\NasaEarthdataService;
use Illuminate\Console\Command;

class CheckAreaRequestTasks extends Command
{
    protected $signature = 'remote-sensing:check-area-tasks 
                            {--complete : Only show completed tasks}';

    protected $description = 'Check status of area request tasks and download completed ones';

    private NasaEarthdataService $service;

    public function __construct(NasaEarthdataService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $this->info('🔍 Checking Area Request Tasks...');
        $this->newLine();

        // Find records with area task IDs
        $records = PlotRemoteSensing::whereNotNull('metadata->area_task_id')
            ->whereNull('area_statistics')
            ->with('plot')
            ->latest('updated_at')
            ->get();

        if ($records->isEmpty()) {
            $this->info('✅ No pending area tasks found');

            return Command::SUCCESS;
        }

        $this->info("📋 Found {$records->count()} pending area task(s)");
        $this->newLine();

        $token = $this->service->getAuthToken();

        if (! $token) {
            $this->error('❌ Failed to authenticate with NASA API');

            return Command::FAILURE;
        }

        $areaService = $this->service->getAreaService();
        $completed = 0;
        $pending = 0;
        $failed = 0;

        foreach ($records as $record) {
            $taskId = $record->metadata['area_task_id'] ?? null;

            if (! $taskId) {
                continue;
            }

            $plot = $record->plot;
            $this->line("📍 {$plot->name} - Task: {$taskId}");

            try {
                // Check task status
                $status = $areaService->checkTaskStatus($taskId, $token);

                if (! $status) {
                    $this->warn('  ⚠️ Could not check status');
                    $failed++;

                    continue;
                }

                $this->line("  Status: {$status['status']} ({$status['progress']}%)");

                if ($status['status'] === 'done') {
                    // Download data
                    $this->line('  ⬇️ Downloading area data...');
                    $areaData = $areaService->downloadAreaData($taskId, $token);

                    if ($areaData) {
                        // Update record with area statistics
                        $record->update([
                            'area_statistics' => $areaData,
                            'data_source' => 'area',
                        ]);

                        $this->info('  ✅ Area statistics saved');
                        $this->line("     Mean NDVI: {$areaData['mean']}");
                        $this->line("     CV: {$areaData['coefficient_variation']}%");
                        $completed++;
                    } else {
                        $this->error('  ❌ Failed to download area data');
                        $failed++;
                    }

                } elseif ($status['status'] === 'error') {
                    $this->error('  ❌ Task failed on NASA side');
                    $failed++;

                    // Clear task ID to prevent re-checking
                    $metadata = $record->metadata;
                    unset($metadata['area_task_id']);
                    $record->update(['metadata' => $metadata]);

                } else {
                    $this->line('  ⏳ Still processing...');
                    $pending++;
                }

                $this->newLine();

            } catch (\Exception $e) {
                $this->error("  ❌ Error: {$e->getMessage()}");
                $failed++;
                $this->newLine();
            }
        }

        // Summary
        $this->info('📊 Task Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Completed & Downloaded', $completed],
                ['⏳ Still Pending', $pending],
                ['❌ Failed/Error', $failed],
            ]
        );

        if ($pending > 0) {
            $this->newLine();
            $this->info('💡 Tip: Run this command again in a few minutes to check pending tasks');
        }

        return Command::SUCCESS;
    }
}
