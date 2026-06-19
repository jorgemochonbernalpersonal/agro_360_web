<?php

namespace App\Livewire\Concerns;

use App\Services\Validators\PacComplianceValidator;

trait WithOfficialReportSummary
{
    public bool $showSummaryModal = false;

    public array $reportSummary = [];

    public function calculateSummary(): void
    {
        if ($this->reportType === 'phytosanitary_treatments') {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);
        } else {
            $this->validate([
                'campaignId' => $this->campaignOwnershipRule(),
            ]);
        }

        try {
            $user = auth()->user();

            if ($this->reportType === 'phytosanitary_treatments') {
                $treatments = \App\Models\PhytosanitaryTreatment::whereHas('activity', function ($q) use ($user) {
                    $q->where('viticulturist_id', $user->id)
                        ->whereBetween('activity_date', [$this->startDate, $this->endDate]);
                })->with(['activity.plot', 'product'])->get();

                $totalTreatments = $treatments->count();

                if ($totalTreatments > 150) {
                    $this->addError('generation', __('Demasiados tratamientos (:count). El límite es 150. Reduce el periodo o contacta con soporte.', ['count' => $totalTreatments]));

                    return;
                }

                if ($totalTreatments === 0) {
                    $this->addError('generation', __('No hay tratamientos fitosanitarios en este periodo.'));

                    return;
                }

                $plots = $treatments->pluck('activity.plot')->unique('id');
                $products = $treatments->pluck('product')->unique('id');
                $totalArea = $treatments->sum('area_treated');

                $validator = new PacComplianceValidator;
                $activities = \App\Models\AgriculturalActivity::ofType('phytosanitary')
                    ->forUser($user->id)
                    ->whereBetween('activity_date', [$this->startDate, $this->endDate])
                    ->with(['plot.sigpacCodes', 'plot.sigpacUses', 'phytosanitaryTreatment.product'])
                    ->get();

                $validation = $validator->validateActivities($activities);

                if (! $validation['is_compliant']) {
                    $errorCount = count($validation['errors']);
                    $this->addError('generation', __('⚠️ El informe contiene :count error(es) PAC que deben corregirse antes de generar (RD 1311/2012).', ['count' => $errorCount]));

                    foreach (array_slice($validation['errors'], 0, 3) as $idx => $error) {
                        $this->addError('pac_error_'.$idx, "Actividad #{$error['activity_id']} ({$error['activity_date']}): ".implode(', ', $error['errors']));
                    }

                    if ($errorCount > 3) {
                        $this->addError('pac_error_3', '... y '.($errorCount - 3).' error(es) más.');
                    }

                    return;
                }

                $estimatedSizeKb = 150 + ($totalTreatments * 5);

                $this->reportSummary = [
                    'type' => 'phytosanitary_treatments',
                    'period' => \Carbon\Carbon::parse($this->startDate)->format('d/m/Y').' - '.\Carbon\Carbon::parse($this->endDate)->format('d/m/Y'),
                    'total_treatments' => $totalTreatments,
                    'plots_count' => $plots->count(),
                    'products_count' => $products->count(),
                    'total_area' => round($totalArea, 2),
                    'estimated_size' => $estimatedSizeKb.' KB',
                    'estimated_time' => $totalTreatments < 20 ? '5-10' : ($totalTreatments < 50 ? '10-15' : '15-30'),
                    'pac_warnings' => $validation['has_warnings'] ? count($validation['warnings']) : 0,
                    'pac_compliance' => round($validator->getCompliancePercentage($validation), 1),
                ];
            } else {
                $campaign = \App\Models\Campaign::findOrFail($this->campaignId);
                $totalActivities = \App\Models\AgriculturalActivity::where('viticulturist_id', $user->id)
                    ->where('campaign_id', $campaign->id)
                    ->count();

                if ($totalActivities === 0) {
                    $this->addError('generation', __('No hay actividades registradas en esta campaña.'));

                    return;
                }

                $estimatedSizeKb = 200 + ($totalActivities * 4);

                if ($totalActivities > 200) {
                    $this->batchPeriods = $this->calculatePeriods($campaign, $totalActivities);
                    $this->totalBatches = count($this->batchPeriods);
                    $this->showBatchOption = true;

                    $this->reportSummary = [
                        'type' => 'full_digital_notebook',
                        'campaign' => $campaign->name.' ('.$campaign->year.')',
                        'total_activities' => $totalActivities,
                        'estimated_size' => $estimatedSizeKb.' KB',
                        'estimated_time' => '5-10 min por lote',
                        'batch_mode' => true,
                    ];
                } else {
                    $this->showBatchOption = false;

                    $this->reportSummary = [
                        'type' => 'full_digital_notebook',
                        'campaign' => $campaign->name.' ('.$campaign->year.')',
                        'total_activities' => $totalActivities,
                        'estimated_size' => $estimatedSizeKb.' KB',
                        'estimated_time' => $totalActivities < 30 ? '10-15' : ($totalActivities < 80 ? '15-25' : '25-40'),
                    ];
                }
            }

            $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;
            $this->showSummaryModal = true;

        } catch (\Exception $e) {
            $this->addError('generation', __('Error al calcular resumen: :message', ['message' => $e->getMessage()]));
        }
    }

    public function closeSummaryModal(): void
    {
        $this->showSummaryModal = false;
        $this->reportSummary = [];
        $this->password = '';
        $this->showBatchOption = false;
        $this->batchPeriods = [];
        $this->totalBatches = 0;
        $this->resetValidation('password');
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;
    }
}
