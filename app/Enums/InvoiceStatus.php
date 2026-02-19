<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Partial   = 'partial';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';
    case Void      = 'void';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Sent      => 'Sent',
            self::Partial   => 'Partial',
            self::Paid      => 'Paid',
            self::Overdue   => 'Overdue',
            self::Cancelled => 'Cancelled',
            self::Void      => 'Void',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft     => 'secondary',
            self::Sent      => 'info',
            self::Partial   => 'warning',
            self::Paid      => 'success',
            self::Overdue   => 'danger',
            self::Cancelled => 'secondary',
            self::Void      => 'secondary',
        };
    }
}
