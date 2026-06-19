<?php

namespace App\Livewire\Winery\Wines\Traits;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

trait HasWineDiagram
{
    private function generateQrSvg(): string
    {
        if (! $this->wine->trace_token) {
            $this->wine->trace_token = (string) Str::uuid();
            $this->wine->saveQuietly();
        }

        $url = route('wine.trace', $this->wine->trace_token);

        $renderer = new ImageRenderer(
            new RendererStyle(280),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($url);
    }

    private function buildDiagram($composition, $processes, $transfers, $losses, $analyses): string
    {
        $lines = ['graph LR'];

        foreach ($composition as $i => $entry) {
            $variety = $entry->harvest->plotPlanting?->grapeVariety->name ?? 'Uva';
            $kg = number_format($entry->quantity_kg ?? 0, 0);
            $nodeId = 'grape_'.$i;
            $label = addslashes("{$variety}\n{$kg} kg");
            $lines[] = "    {$nodeId}([\"🍇 {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#d1fae5,stroke:#059669,color:#065f46";
            $lines[] = "    {$nodeId} --> wine";
        }

        $wineName = addslashes($this->wine->name);
        $lines[] = "    wine[\"🍷 {$wineName}\"]";
        $lines[] = '    style wine fill:#ede9fe,stroke:#7c3aed,color:#4c1d95,font-weight:bold';

        $processLabels = [
            'destemming_crushing' => __('Despalillado/Estrujado'),
            'pressing' => __('Prensado'),
            'settling' => __('Desfangado'),
            'fermentation' => __('Fermentación'),
            'maceration' => __('Maceración'),
            'malolactic' => __('Maloláctica'),
            'aging' => __('Crianza'),
            'racking' => __('Trasiego'),
            'blending' => __('Coupage'),
            'fining' => __('Clarificación'),
            'filtration' => __('Filtración'),
            'cold_stabilization' => __('Est. en frío'),
            'bottling' => __('Embotellado'),
            'other' => __('Otro'),
        ];

        $prevNode = 'wine';
        foreach ($processes as $i => $proc) {
            $nodeId = 'proc_'.$i;
            $label = $processLabels[$proc->process_type] ?? $proc->process_type;
            $date = $proc->start_date ? \Carbon\Carbon::parse($proc->start_date)->format('d/m') : '';
            $lines[] = "    {$nodeId}[\"⚙️ {$label}".($date ? "\n{$date}" : '').'"]';
            $lines[] = "    style {$nodeId} fill:#dbeafe,stroke:#2563eb,color:#1e3a8a";
            $lines[] = "    {$prevNode} --> {$nodeId}";
            $prevNode = $nodeId;
        }

        foreach ($transfers as $i => $tr) {
            $from = $tr->fromContainer->name ?? '?';
            $to = $tr->toContainer->name ?? '?';
            $nodeId = 'tr_'.$i;
            $label = addslashes("Trasvase\n{$from} → {$to}");
            $lines[] = "    {$nodeId}[\"↔️ {$label}\"]";
            $lines[] = "    style {$nodeId} fill:#fef9c3,stroke:#ca8a04,color:#78350f";
            $lines[] = "    wine --> {$nodeId}";
        }

        foreach ($losses as $i => $lo) {
            $nodeId = 'loss_'.$i;
            $qty = number_format($lo->quantity ?? 0, 1);
            $unit = $lo->unitOfMeasurement->abbreviation ?? '';
            $label = addslashes("Merma\n{$qty} {$unit}");
            $lines[] = "    {$nodeId}([\"⚠️ {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#fee2e2,stroke:#dc2626,color:#7f1d1d";
            $lines[] = "    wine -.-> {$nodeId}";
        }

        foreach ($analyses as $i => $an) {
            $nodeId = 'an_'.$i;
            $date = $an->analysis_date ? $an->analysis_date->format('d/m/y') : '';
            $label = addslashes("Análisis\n{$date}");
            $lines[] = "    {$nodeId}([\"🔬 {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#fef3c7,stroke:#d97706,color:#78350f";
            $lines[] = "    wine -.-> {$nodeId}";
        }

        return implode("\n", $lines);
    }

    private function buildTimeline($fermentationControls, $transfers, $losses, $analyses): array
    {
        $items = collect();

        foreach ($fermentationControls as $fc) {
            $items->push(['type' => 'fermentation', 'date' => $fc->control_date, 'model' => $fc]);
        }
        foreach ($transfers as $tr) {
            $items->push(['type' => 'transfer', 'date' => $tr->transfer_date->startOfDay(), 'model' => $tr]);
        }
        foreach ($losses as $lo) {
            $items->push(['type' => 'loss', 'date' => $lo->loss_date->startOfDay(), 'model' => $lo]);
        }
        foreach ($analyses as $an) {
            $items->push(['type' => 'analysis', 'date' => $an->analysis_date->startOfDay(), 'model' => $an]);
        }

        return $items
            ->sortByDesc('date')
            ->groupBy(fn ($item) => $item['date']->format('Y-m-d'))
            ->all();
    }
}
