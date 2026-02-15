<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Reports\ReportExporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $exportType,
        public string $format,
        public array $filters = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting data export job', [
            'user_id' => $this->user->id,
            'export_type' => $this->exportType,
            'format' => $this->format,
        ]);

        try {
            $data = $this->getData();
            $filename = $this->generateFilename();

            $exported = match ($this->format) {
                'csv' => $this->exportToCsv($data, $filename),
                'xml' => $this->exportToXml($data, $filename),
                'json' => $this->exportToJson($data, $filename),
                default => throw new \InvalidArgumentException("Invalid export format: {$this->format}")
            };

            Log::info('Data exported successfully', [
                'user_id' => $this->user->id,
                'filename' => $filename,
                'records' => count($data),
            ]);

            // TODO: Notificar al usuario que la exportación está lista para descargar
        } catch (\Exception $e) {
            Log::error('Failed to export data', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get data to export based on type
     */
    protected function getData(): array
    {
        return match ($this->exportType) {
            'activities' => $this->user->agriculturalActivities()
                ->with(['plot', 'campaign'])
                ->when(isset($this->filters['campaign_id']), function ($q) {
                    $q->where('campaign_id', $this->filters['campaign_id']);
                })
                ->get()
                ->toArray(),
            'plots' => $this->user->plots()->with(['province', 'municipality'])->get()->toArray(),
            'invoices' => $this->user->invoices()->with('items')->get()->toArray(),
            default => throw new \InvalidArgumentException("Invalid export type: {$this->exportType}")
        };
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(): string
    {
        return sprintf(
            'exports/%s_%s_%s.%s',
            $this->exportType,
            $this->user->id,
            now()->format('Y-m-d_His'),
            $this->format
        );
    }

    /**
     * Export data to CSV
     */
    protected function exportToCsv(array $data, string $filename): string
    {
        $csv = [];
        
        if (count($data) > 0) {
            $csv[] = array_keys($data[0]);
            foreach ($data as $row) {
                $csv[] = array_values($row);
            }
        }

        $content = implode("\n", array_map(function ($row) {
            return implode(';', $row);
        }, $csv));

        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Export data to XML
     */
    protected function exportToXml(array $data, string $filename): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
        
        foreach ($data as $item) {
            $record = $xml->addChild('record');
            foreach ($item as $key => $value) {
                $record->addChild($key, htmlspecialchars($value ?? ''));
            }
        }

        Storage::put($filename, $xml->asXML());
        
        return $filename;
    }

    /**
     * Export data to JSON
     */
    protected function exportToJson(array $data, string $filename): string
    {
        Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $filename;
    }
}
