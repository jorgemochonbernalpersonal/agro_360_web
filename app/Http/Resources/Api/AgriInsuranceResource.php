<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgriInsuranceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'policy_number'     => $this->policy_number,
            'insurance_company' => $this->insurance_company,
            'coverage_type'     => $this->coverage_type,
            'start_date'        => $this->start_date?->toDateString(),
            'end_date'          => $this->end_date?->toDateString(),
            'insured_amount'    => $this->insured_amount !== null ? (float) $this->insured_amount : null,
            'premium'           => $this->premium !== null ? (float) $this->premium : null,
            'subsidy_amount'    => $this->subsidy_amount !== null ? (float) $this->subsidy_amount : null,
            'status'            => $this->status,
            'agent_name'        => $this->agent_name,
            'agent_phone'       => $this->agent_phone,
            'covered_plots'     => $this->covered_plots,
            'notes'             => $this->notes,
        ];
    }
}
