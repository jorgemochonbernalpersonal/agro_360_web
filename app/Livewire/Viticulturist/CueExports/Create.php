<?php

namespace App\Livewire\Viticulturist\CueExports;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\CueExport;
use App\Models\Exploitation;

class Create extends AbstractCreate
{
    public string $exploitation_id = '';
    public int    $campaign_year   = 0;
    public string $period_type     = 'annual';
    public string $from_date       = '';
    public string $to_date         = '';

    public function mount(): void
    {
        $this->campaign_year = now()->year;
        $this->from_date     = now()->startOfYear()->format('Y-m-d');
        $this->to_date       = now()->endOfYear()->format('Y-m-d');
    }

    public function updatedPeriodType(string $value): void
    {
        if ($value === 'annual') {
            $this->from_date = now()->setYear($this->campaign_year)->startOfYear()->format('Y-m-d');
            $this->to_date   = now()->setYear($this->campaign_year)->endOfYear()->format('Y-m-d');
        }
    }

    public function updatedCampaignYear(int $value): void
    {
        if ($this->period_type === 'annual' && $value >= 2000) {
            $this->from_date = now()->setYear($value)->startOfYear()->format('Y-m-d');
            $this->to_date   = now()->setYear($value)->endOfYear()->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'exploitation_id' => 'required|exists:exploitations,id',
            'campaign_year'   => 'required|integer|min:2000|max:' . (now()->year + 1),
            'period_type'     => 'required|in:annual,quarterly',
            'from_date'       => 'required|date',
            'to_date'         => 'required|date|after_or_equal:from_date',
        ];
    }

    protected function performCreate(): void
    {
        $exploitation = Exploitation::where('viticulturist_id', $this->viticulturistId())
            ->findOrFail($this->exploitation_id);

        CueExport::create([
            'exploitation_id'  => $exploitation->id,
            'viticulturist_id' => $this->viticulturistId(),
            'campaign_year'    => $this->campaign_year,
            'period_type'      => $this->period_type,
            'from_date'        => $this->from_date,
            'to_date'          => $this->to_date,
            'status'           => 'draft',
        ]);
    }

    protected function successMessage(): string
    {
        return __('Exportación CUE creada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.cue-exports.index';
    }

    protected function viewData(): array
    {
        return [
            'exploitations' => Exploitation::where('viticulturist_id', $this->viticulturistId())
                ->where('active', true)
                ->orderBy('exploitation_name')
                ->get(),
        ];
    }
}
