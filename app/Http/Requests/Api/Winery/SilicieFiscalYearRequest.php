<?php

namespace App\Http\Requests\Api\Winery;

class SilicieFiscalYearRequest extends WineryApiRequest
{
    public function rules(): array
    {
        return [
            'fiscal_year' => 'nullable|integer|min:1990|max:'.(now()->year + 1),
        ];
    }
}
