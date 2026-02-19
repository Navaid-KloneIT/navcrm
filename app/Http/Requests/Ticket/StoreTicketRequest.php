<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', new Enum(TicketPriority::class)],
            'channel'     => ['required', new Enum(TicketChannel::class)],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
