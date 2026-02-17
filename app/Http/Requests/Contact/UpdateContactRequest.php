<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:50'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'department' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'twitter_handle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'facebook_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'address_line_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_id' => ['sometimes', 'nullable', 'exists:users,id'],
        ];
    }
}
