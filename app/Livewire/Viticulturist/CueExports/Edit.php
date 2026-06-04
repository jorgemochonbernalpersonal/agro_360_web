<?php

namespace App\Livewire\Viticulturist\CueExports;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\CueExport;
use App\Models\Exploitation;

class Edit extends AbstractEdit
{
    use WithRoleAwareRedirect;

    public CueExport $cueExport;

    public string $exploitation_id = '';

    public int $campaign_year = 0;

    public string $period_type = 'annual';

    public string $from_date = '';

    public string $to_date = '';

    public function mount(CueExport $cueExport): void
    {
        $this->authorizeOwnership($cueExport);

        if ($cueExport->status !== 'draft') {
            session()->flash('error', __('Solo se pueden editar exportaciones en estado Borrador.'));
            $this->viticulturistRoleRedirect('cue-exports.index');

            return;
        }

        $this->cueExport = $cueExport;
        $this->exploitation_id = (string) $cueExport->exploitation_id;
        $this->campaign_year = $cueExport->campaign_year;
        $this->period_type = $cueExport->period_type;
        $this->from_date = $cueExport->from_date->format('Y-m-d');
        $this->to_date = $cueExport->to_date->format('Y-m-d');
    }

    public function updatedPeriodType(string $value): void
    {
        if ($value === 'annual') {
            $this->from_date = now()->setYear($this->campaign_year)->startOfYear()->format('Y-m-d');
            $this->to_date = now()->setYear($this->campaign_year)->endOfYear()->format('Y-m-d');
        }
    }

    public function updatedCampaignYear(int $value): void
    {
        if ($this->period_type === 'annual' && $value >= 2000) {
            $this->from_date = now()->setYear($value)->startOfYear()->format('Y-m-d');
            $this->to_date = now()->setYear($value)->endOfYear()->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'exploitation_id' => 'required|exists:exploitations,id',
            'campaign_year' => 'required|integer|min:2000|max:'.(now()->year + 1),
            'period_type' => 'required|in:annual,quarterly',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ];
    }

    protected function performUpdate(): void
    {
        $exploitation = Exploitation::where('viticulturist_id', $this->viticulturistId())
            ->findOrFail($this->exploitation_id);

        $this->cueExport->update([
            'exploitation_id' => $exploitation->id,
            'campaign_year' => $this->campaign_year,
            'period_type' => $this->period_type,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Exportación CUE actualizada.');
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
