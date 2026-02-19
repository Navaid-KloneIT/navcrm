<?php

namespace App\Http\Requests\CallLog;

use App\Enums\CallDirection;
use App\Enums\CallStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCallLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction'     => ['required', new Enum(CallDirection::class)],
            'status'        => ['required', new Enum(CallStatus::class)],
            'phone_number'  => ['nullable', 'string', 'max:30'],
            'duration'      => ['nullable', 'integer', 'min:0'],
            'recording_url' => ['nullable', 'url', 'max:500'],
            'notes'         => ['nullable', 'string'],
            'called_at'     => ['required', 'date'],
            'loggable_type' => ['nullable', 'string'],
            'loggable_id'   => ['nullable', 'integer'],
        ];
    }
}
