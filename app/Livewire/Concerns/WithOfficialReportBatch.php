<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

trait WithOfficialReportBatch
{
    public bool $showBatchOption = false;

    public array $batchPeriods = [];

    public int $totalBatches = 0;

    public function generateBatchReports(): void
    {
        if (! $this->checkSignaturePassword()) {
            return;
        }

        $campaign = \App\Models\Campaign::findOrFail($this->campaignId);
        $generatedCount = 0;
        $errors = [];

        foreach ($this->batchPeriods as $index => $period) {
            try {
                $report = \App\Models\OfficialReport::create([
                    'user_id' => auth()->id(),
                    'report_type' => 'full_digital_notebook',
                    'period_start' => $period['start'],
                    'period_end' => $period['end'],
                    'report_metadata' => [
                        'campaign_id' => $this->campaignId,
                        'campaign_name' => $campaign->name,
                        'batch_index' => $index + 1,
                        'total_batches' => count($this->batchPeriods),
                        'period_label' => $period['label'],
                    ],
                    'verification_code' => \App\Models\OfficialReport::generateVerificationCode(),
                    'processing_status' => 'pending',
                    'signature_hash' => 'TEMP_'.uniqid().'_'.time(),
                    'signed_at' => now(),
                    'signed_ip' => request()->ip(),
                ]);

                \App\Jobs\GenerateOfficialReportJob::dispatch(
                    $report->id,
                    auth()->id(),
                    'full_digital_notebook',
                    [
                        'campaign_id' => $this->campaignId,
                        'start_date' => $period['start'],
                        'end_date' => $period['end'],
                    ],
                    $this->password
                );

                $generatedCount++;
            } catch (\Exception $e) {
                \Log::error('Error generando informe por lotes', [
                    'period' => $period['label'],
                    'error' => $e->getMessage(),
                ]);
                $errors[] = "Error en {$period['label']}: ".$e->getMessage();
            }
        }

        if ($generatedCount > 0) {
            $this->password = '';
            $this->toastSuccess("✅ Se generarán {$generatedCount} informes en lotes. Te avisaremos por email cuando estén listos (5-10 min por lote).");

            $this->viticulturistRoleRedirect('official-reports.index');
        } else {
            $this->addError('generation', __('Error al generar informes: :list', ['list' => implode(', ', $errors)]));
            $this->toastError(__('Error al generar informes por lotes.'));
        }
    }

    public function forceGenerateSingle(): void
    {
        $this->showBatchOption = false;
    }

    private function calculatePeriods($campaign, int $totalActivities): array
    {
        $start = Carbon::parse($campaign->start_date);
        $end = Carbon::parse($campaign->end_date);

        return $start->diffInDays($end) < 180
            ? $this->splitByMonths($start, $end, $campaign->id)
            : $this->splitByQuarters($start, $end, $campaign->id);
    }

    private function splitByMonths(Carbon $start, Carbon $end, int $campaignId): array
    {
        $periods = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->endOfMonth();
            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            $count = \App\Models\AgriculturalActivity::forUser(auth()->id())
                ->forCampaign($campaignId)
                ->whereBetween('activity_date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->count();

            if ($count > 0) {
                $periods[] = [
                    'label' => $periodStart->format('M Y'),
                    'start' => $periodStart->format('Y-m-d'),
                    'end' => $periodEnd->format('Y-m-d'),
                    'count' => $count,
                ];
            }

            $current->addMonth()->startOfMonth();
            if ($current->gt($end)) {
                break;
            }
        }

        return $periods;
    }

    private function splitByQuarters(Carbon $start, Carbon $end, int $campaignId): array
    {
        $periods = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->addMonths(3)->subDay();
            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            $count = \App\Models\AgriculturalActivity::forUser(auth()->id())
                ->forCampaign($campaignId)
                ->whereBetween('activity_date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->count();

            if ($count > 0) {
                $quarter = (int) ceil($periodStart->month / 3);
                $periods[] = [
                    'label' => "Q{$quarter} {$periodStart->year}",
                    'start' => $periodStart->format('Y-m-d'),
                    'end' => $periodEnd->format('Y-m-d'),
                    'count' => $count,
                ];
            }

            $current->addMonths(3)->startOfMonth();
            if ($current->gt($end)) {
                break;
            }
        }

        return $periods;
    }
}
