<?php

namespace App\Http\Requests\CalendarEvent;

use App\Enums\CalendarEventStatus;
use App\Enums\CalendarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                     => ['required', 'string', 'max:255'],
            'description'               => ['nullable', 'string'],
            'event_type'                => ['required', new Enum(CalendarEventType::class)],
            'status'                    => ['required', new Enum(CalendarEventStatus::class)],
            'starts_at'                 => ['required', 'date'],
            'ends_at'                   => ['required', 'date', 'after_or_equal:starts_at'],
            'is_all_day'                => ['boolean'],
            'location'                  => ['nullable', 'string', 'max:255'],
            'meeting_link'              => ['nullable', 'url', 'max:500'],
            'invite_url'                => ['nullable', 'url', 'max:500'],
            'external_calendar_id'      => ['nullable', 'string', 'max:255'],
            'external_calendar_source'  => ['nullable', 'string', 'in:google,outlook,ical'],
            'eventable_type'            => ['nullable', 'string'],
            'eventable_id'              => ['nullable', 'integer'],
            'organizer_id'              => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
