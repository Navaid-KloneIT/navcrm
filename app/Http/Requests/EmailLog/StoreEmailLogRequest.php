<?php

namespace App\Http\Requests\EmailLog;

use App\Enums\EmailDirection;
use App\Enums\EmailSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreEmailLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction'      => ['required', new Enum(EmailDirection::class)],
            'source'         => ['required', new Enum(EmailSource::class)],
            'subject'        => ['required', 'string', 'max:255'],
            'body'           => ['nullable', 'string'],
            'from_email'     => ['nullable', 'email', 'max:255'],
            'to_email'       => ['nullable', 'email', 'max:255'],
            'cc'             => ['nullable', 'array'],
            'cc.*'           => ['email'],
            'message_id'     => ['nullable', 'string', 'max:255'],
            'sent_at'        => ['nullable', 'date'],
            'opened_at'      => ['nullable', 'date'],
            'clicked_at'     => ['nullable', 'date'],
            'emailable_type' => ['nullable', 'string'],
            'emailable_id'   => ['nullable', 'integer'],
        ];
    }
}
