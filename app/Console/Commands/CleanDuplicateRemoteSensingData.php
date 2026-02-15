<?php

namespace App\Console\Commands;

use App\Models\PlotRemoteSensing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateRemoteSensingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remote-sensing:clean-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean duplicate remote sensing data entries (same plot_id and image_date)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Searching for duplicate remote sensing data...');

        // Find duplicates (same plot_id and image_date)
        $duplicates = DB::table('plot_remote_sensing')
            ->select('plot_id', 'image_date', DB::raw('COUNT(*) as count'))
            ->groupBy('plot_id', 'image_date')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicates found!');
            return self::SUCCESS;
        }

        $this->warn("Found {$duplicates->count()} duplicate groups");

        $bar = $this->output->createProgressBar($duplicates->count());
        $bar->start();

        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            // Get all records for this plot_id and date
            $records = PlotRemoteSensing::where('plot_id', $duplicate->plot_id)
                ->where('image_date', $duplicate->image_date)
                ->orderBy('id', 'desc')
                ->get();

            // Keep the most recent one (highest ID), delete the rest
            $keep = $records->first();
            $toDelete = $records->skip(1);

            foreach ($toDelete as $record) {
                $record->delete();
                $totalDeleted++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Cleaned {$totalDeleted} duplicate records!");

        return self::SUCCESS;
    }
}
