<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft     = 'draft';
    case Submitted = 'submitted';
    case Approved  = 'approved';
    case Received  = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Submitted => 'Submitted',
            self::Approved  => 'Approved',
            self::Received  => 'Received',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'secondary',
            self::Submitted => 'info',
            self::Approved  => 'primary',
            self::Received  => 'success',
            self::Cancelled => 'danger',
        };
    }
}
