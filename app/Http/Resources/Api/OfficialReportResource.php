<?php

namespace App\Http\Resources\Api;

use App\Models\OfficialReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OfficialReport */
class OfficialReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_type' => $this->report_type,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'is_valid' => (bool) $this->is_valid,
            'processing_status' => $this->processing_status,
            'verification_code' => $this->verification_code,
            'pdf_filename' => $this->pdf_filename,
            'signed_at' => $this->signed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
