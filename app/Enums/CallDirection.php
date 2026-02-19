<?php

namespace App\Enums;

enum CallDirection: string
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
            self::Inbound  => 'bi-telephone-inbound',
            self::Outbound => 'bi-telephone-outbound',
        };
    }
}
