<?php

namespace App\Enums;

enum CallStatus: string
{
    case Completed = 'completed';
    case NoAnswer  = 'no_answer';
    case Busy      = 'busy';
    case Voicemail = 'voicemail';
    case Failed    = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Completed => 'Completed',
            self::NoAnswer  => 'No Answer',
            self::Busy      => 'Busy',
            self::Voicemail => 'Voicemail',
            self::Failed    => 'Failed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Completed => 'success',
            self::NoAnswer  => 'warning',
            self::Busy      => 'warning',
            self::Voicemail => 'info',
            self::Failed    => 'danger',
        };
    }
}
