<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case Meeting   = 'meeting';
    case Call      = 'call';
    case Demo      = 'demo';
    case FollowUp  = 'follow_up';
    case Webinar   = 'webinar';
    case Other     = 'other';

    public function label(): string
    {
        return match($this) {
            self::Meeting  => 'Meeting',
            self::Call     => 'Call',
            self::Demo     => 'Demo',
            self::FollowUp => 'Follow-Up',
            self::Webinar  => 'Webinar',
            self::Other    => 'Other',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Meeting  => 'primary',
            self::Call     => 'success',
            self::Demo     => 'info',
            self::FollowUp => 'warning',
            self::Webinar  => 'purple',
            self::Other    => 'secondary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Meeting  => 'bi-people',
            self::Call     => 'bi-telephone',
            self::Demo     => 'bi-display',
            self::FollowUp => 'bi-arrow-repeat',
            self::Webinar  => 'bi-camera-video',
            self::Other    => 'bi-calendar-event',
        };
    }
}
