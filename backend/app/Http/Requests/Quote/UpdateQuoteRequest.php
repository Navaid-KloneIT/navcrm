<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opportunity_id' => ['nullable', 'exists:opportunities,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'valid_until' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'line_items' => ['sometimes', 'array', 'min:1'],
            'line_items.*.product_id' => ['nullable', 'exists:products,id'],
            'line_items.*.description' => ['required', 'string', 'max:500'],
            'line_items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'line_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'line_items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
