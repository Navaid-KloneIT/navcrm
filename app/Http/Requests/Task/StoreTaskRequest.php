<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'due_date'             => ['nullable', 'date'],
            'due_time'             => ['nullable', 'date_format:H:i'],
            'priority'             => ['required', new Enum(TaskPriority::class)],
            'status'               => ['required', new Enum(TaskStatus::class)],
            'is_recurring'         => ['boolean'],
            'recurrence_type'      => ['nullable', 'required_if:is_recurring,true', new Enum(TaskRecurrence::class)],
            'recurrence_interval'  => ['nullable', 'integer', 'min:1', 'max:99'],
            'recurrence_ends_at'   => ['nullable', 'date', 'after:due_date'],
            'taskable_type'        => ['nullable', 'string'],
            'taskable_id'          => ['nullable', 'integer'],
            'assigned_to'          => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
