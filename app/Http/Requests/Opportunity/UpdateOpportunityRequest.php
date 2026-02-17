<?php

namespace App\Http\Requests\Opportunity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'close_date' => ['nullable', 'date'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'pipeline_stage_id' => ['sometimes', 'exists:pipeline_stages,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'description' => ['nullable', 'string'],
            'next_steps' => ['nullable', 'string'],
            'competitor' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'lost_reason' => ['nullable', 'string'],
        ];
    }
}
