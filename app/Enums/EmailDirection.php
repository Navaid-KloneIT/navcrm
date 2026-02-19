<?php

namespace App\Enums;

enum EmailDirection: string
{
    case Inbound  = 'inbound';
    case Outbound = 'outbound';

    public function label(): string
    {
        return match($this) {
            self::Inbound  => 'Inbound',
            self::Outbound => 'Outbound',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Inbound  => 'bi-envelope-arrow-down',
            self::Outbound => 'bi-envelope-arrow-up',
        };
    }
}
