<?php

namespace App\Enums;

enum TicketChannel: string
{
    case Email  = 'email';
    case Portal = 'portal';
    case Phone  = 'phone';
    case Manual = 'manual';

    public function label(): string
    {
        return match($this) {
            self::Email  => 'Email',
            self::Portal => 'Portal',
            self::Phone  => 'Phone',
            self::Manual => 'Manual',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Email  => 'bi-envelope',
            self::Portal => 'bi-globe',
            self::Phone  => 'bi-telephone',
            self::Manual => 'bi-pencil',
        };
    }
}
