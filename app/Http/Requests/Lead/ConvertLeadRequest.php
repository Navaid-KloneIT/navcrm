<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'create_account' => ['nullable', 'boolean'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'existing_account_id' => ['nullable', 'exists:accounts,id'],
        ];
    }
}
