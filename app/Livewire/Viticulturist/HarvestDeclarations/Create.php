<?php

namespace App\Livewire\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;

class Create extends AbstractCreate
{
    public string $campaign_id       = '';
    public string $declaration_year  = '';
    public string $declaration_date  = '';
    public string $authority         = '';
    public string $total_surface_ha  = '';
    public string $total_kg          = '';
    public string $notes             = '';

    // Lines: array of {variety, plot_name, surface_ha, kg, destination, rega_code, buyer}
    public array $lines = [];

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
        $this->campaign_id      = (string) ($campaign?->id ?? '');
        $this->declaration_year = (string) now()->year;
        $this->declaration_date = now()->format('Y-m-d');
        $this->lines            = [self::emptyLine()];
    }

    public static function emptyLine(): array
    {
        return [
            'variety'     => '',
            'plot_name'   => '',
            'surface_ha'  => '',
            'kg'          => '',
            'destination' => '',
            'rega_code'   => '',
            'buyer'       => '',
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = self::emptyLine();
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 1) {
            array_splice($this->lines, $index, 1);
        }
    }

    public function updatedLines(): void
    {
        $this->recalculateTotals();
    }

    protected function recalculateTotals(): void
    {
        $totalSurface = 0;
        $totalKg      = 0;
        foreach ($this->lines as $line) {
            $totalSurface += (float) ($line['surface_ha'] ?? 0);
            $totalKg      += (float) ($line['kg'] ?? 0);
        }
        $this->total_surface_ha = $totalSurface > 0 ? (string) round($totalSurface, 4) : '';
        $this->total_kg         = $totalKg > 0      ? (string) round($totalKg, 2)      : '';
    }

    protected function rules(): array
    {
        return [
            'campaign_id'      => 'required|exists:campaigns,id',
            'declaration_year' => 'required|integer|min:2000|max:2100',
            'declaration_date' => 'required|date',
            'authority'        => 'required|string|max:255',
            'total_surface_ha' => 'nullable|numeric|min:0',
            'total_kg'         => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
            'lines'            => 'nullable|array',
            'lines.*.variety'  => 'nullable|string|max:100',
            'lines.*.plot_name'=> 'nullable|string|max:150',
            'lines.*.surface_ha'=> 'nullable|numeric|min:0',
            'lines.*.kg'       => 'nullable|numeric|min:0',
            'lines.*.destination'=> 'nullable|string|max:150',
            'lines.*.rega_code'=> 'nullable|string|max:30',
            'lines.*.buyer'    => 'nullable|string|max:150',
        ];
    }

    protected function performCreate(): void
    {
        $lines = array_filter($this->lines, fn($l) => !empty($l['variety']) || !empty($l['kg']));

        HarvestDeclaration::create([
            'viticulturist_id'  => $this->viticulturistId(),
            'campaign_id'       => $this->campaign_id,
            'declaration_year'  => $this->declaration_year,
            'declaration_date'  => $this->declaration_date,
            'authority'         => $this->authority,
            'total_surface_ha'  => $this->total_surface_ha ?: null,
            'total_kg'          => $this->total_kg ?: null,
            'declaration_lines' => array_values($lines) ?: null,
            'status'            => 'draft',
            'notes'             => $this->notes ?: null,
            'active'            => true,
        ]);
    }

    protected function successMessage(): string
    {
        return 'Declaración de vendimia creada en borrador.';
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.harvest-declarations.index';
    }

    protected function viewData(): array
    {
        return [
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
        ];
    }
}
