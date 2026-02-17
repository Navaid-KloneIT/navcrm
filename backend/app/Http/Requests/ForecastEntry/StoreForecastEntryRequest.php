<?php

namespace App\Http\Requests\ForecastEntry;

use Illuminate\Foundation\Http\FormRequest;

class StoreForecastEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opportunity_id' => ['required', 'exists:opportunities,id'],
            'forecast_category' => ['required', 'in:pipeline,best_case,commit,closed'],
            'amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after:period_start'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
