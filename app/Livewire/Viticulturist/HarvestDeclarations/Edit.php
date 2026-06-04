<?php

namespace App\Livewire\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\AbstractEdit;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;

class Edit extends AbstractEdit
{
    public HarvestDeclaration $declaration;

    public string $campaign_id = '';

    public string $declaration_year = '';

    public string $declaration_date = '';

    public string $submission_date = '';

    public string $authority = '';

    public string $reference_number = '';

    public string $total_surface_ha = '';

    public string $total_kg = '';

    public string $status = 'draft';

    public string $rejection_reason = '';

    public string $notes = '';

    public array $lines = [];

    public function mount(HarvestDeclaration $declaration): void
    {
        $this->authorize($declaration);
        $this->declaration = $declaration;
        $this->campaign_id = (string) $declaration->campaign_id;
        $this->declaration_year = (string) $declaration->declaration_year;
        $this->declaration_date = $declaration->declaration_date->format('Y-m-d');
        $this->submission_date = $declaration->submission_date?->format('Y-m-d') ?? '';
        $this->authority = $declaration->authority;
        $this->reference_number = $declaration->reference_number ?? '';
        $this->total_surface_ha = (string) ($declaration->total_surface_ha ?? '');
        $this->total_kg = (string) ($declaration->total_kg ?? '');
        $this->status = $declaration->status;
        $this->rejection_reason = $declaration->rejection_reason ?? '';
        $this->notes = $declaration->notes ?? '';
        $this->lines = $declaration->declaration_lines ?? [Create::emptyLine()];
    }

    public function addLine(): void
    {
        $this->lines[] = Create::emptyLine();
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 1) {
            array_splice($this->lines, $index, 1);
        }
    }

    public function updatedLines(): void
    {
        $totalSurface = 0;
        $totalKg = 0;
        foreach ($this->lines as $line) {
            $totalSurface += (float) ($line['surface_ha'] ?? 0);
            $totalKg += (float) ($line['kg'] ?? 0);
        }
        $this->total_surface_ha = $totalSurface > 0 ? (string) round($totalSurface, 4) : '';
        $this->total_kg = $totalKg > 0 ? (string) round($totalKg, 2) : '';
    }

    protected function rules(): array
    {
        return [
            'campaign_id' => $this->campaignOwnershipRule(),
            'declaration_year' => 'required|integer|min:2000|max:2100',
            'declaration_date' => 'required|date',
            'submission_date' => 'nullable|date',
            'authority' => 'required_unless:status,draft|nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'total_surface_ha' => 'nullable|numeric|min:0',
            'total_kg' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,submitted,accepted,rejected',
            'rejection_reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.variety' => 'nullable|string|max:100',
            'lines.*.plot_name' => 'nullable|string|max:150',
            'lines.*.surface_ha' => 'nullable|numeric|min:0',
            'lines.*.kg' => 'nullable|numeric|min:0',
            'lines.*.destination' => 'nullable|string|max:150',
            'lines.*.rega_code' => 'nullable|string|max:30',
            'lines.*.buyer' => 'nullable|string|max:150',
        ];
    }

    protected function performUpdate(): void
    {
        $lines = array_filter($this->lines, fn ($l) => ! empty($l['variety']) || ! empty($l['kg']));

        $this->declaration->update([
            'campaign_id' => $this->campaign_id,
            'declaration_year' => $this->declaration_year,
            'declaration_date' => $this->declaration_date,
            'submission_date' => $this->submission_date ?: null,
            'authority' => $this->authority,
            'reference_number' => $this->reference_number ?: null,
            'total_surface_ha' => $this->total_surface_ha ?: null,
            'total_kg' => $this->total_kg ?: null,
            'declaration_lines' => array_values($lines) ?: null,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Declaración actualizada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.harvest-declarations.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'statuses' => HarvestDeclaration::statusOptions(),
        ];
    }
}
