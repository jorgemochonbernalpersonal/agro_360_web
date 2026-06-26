<?php

namespace App\Services;

use App\Models\PacDeclaration;
use App\Models\PacDeclarationItem;
use Illuminate\Support\Facades\DB;

class PacDeclarationService
{
    /**
     * @param array{reference_number: string, notes: string, status: string}                    $fields
     * @param array<int, array{declared_area: mixed, eligible_area: mixed, eco_schemes: array}> $selectedItems keyed by plot_id
     */
    public function create(int $viticulturistId, int $year, array $fields, array $selectedItems): PacDeclaration
    {
        return DB::transaction(function () use ($viticulturistId, $year, $fields, $selectedItems) {
            $declaration = PacDeclaration::create([
                'viticulturist_id' => $viticulturistId,
                'year' => $year,
                'reference_number' => $fields['reference_number'] ?: null,
                'status' => $fields['status'],
                'submitted_at' => $fields['status'] === 'submitted' ? now() : null,
                'notes' => $fields['notes'] ?: null,
                'total_declared_area' => 0,
                'total_eligible_area' => 0,
            ]);

            foreach ($selectedItems as $plotId => $item) {
                PacDeclarationItem::create([
                    'declaration_id' => $declaration->id,
                    'plot_id' => $plotId,
                    'declared_area' => $item['declared_area'],
                    'eligible_area' => $item['eligible_area'],
                    'eco_schemes' => ! empty($item['eco_schemes']) ? $item['eco_schemes'] : null,
                ]);
            }

            $declaration->recalculateTotals();

            return $declaration;
        });
    }

    /**
     * @param array{reference_number: string, notes: string, status: string}                    $fields
     * @param array<int, array{declared_area: mixed, eligible_area: mixed, eco_schemes: array}> $selectedItems keyed by plot_id
     */
    public function update(PacDeclaration $declaration, array $fields, array $selectedItems): void
    {
        DB::transaction(function () use ($declaration, $fields, $selectedItems) {
            $declaration->update([
                'reference_number' => $fields['reference_number'] ?: null,
                'status' => $fields['status'],
                'submitted_at' => $fields['status'] === 'submitted' ? now() : null,
                'notes' => $fields['notes'] ?: null,
            ]);

            $selectedIds = array_keys($selectedItems);
            $declaration->items()->whereNotIn('plot_id', $selectedIds)->delete();

            foreach ($selectedItems as $plotId => $item) {
                PacDeclarationItem::updateOrCreate(
                    ['declaration_id' => $declaration->id, 'plot_id' => $plotId],
                    [
                        'declared_area' => $item['declared_area'],
                        'eligible_area' => $item['eligible_area'],
                        'eco_schemes' => ! empty($item['eco_schemes']) ? $item['eco_schemes'] : null,
                    ]
                );
            }

            $declaration->recalculateTotals();
        });
    }
}
