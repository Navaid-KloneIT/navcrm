<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'     => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', new Enum(TicketStatus::class)],
            'priority'    => ['sometimes', new Enum(TicketPriority::class)],
            'channel'     => ['sometimes', new Enum(TicketChannel::class)],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
