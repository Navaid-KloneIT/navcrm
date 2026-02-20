<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft    = 'draft';
    case Sent     = 'sent';
    case Viewed   = 'viewed';
    case Signed   = 'signed';
    case Rejected = 'rejected';
    case Expired  = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Viewed   => 'Viewed',
            self::Signed   => 'Signed',
            self::Rejected => 'Rejected',
            self::Expired  => 'Expired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft    => 'secondary',
            self::Sent     => 'info',
            self::Viewed   => 'primary',
            self::Signed   => 'success',
            self::Rejected => 'danger',
            self::Expired  => 'warning',
        };
    }
}
