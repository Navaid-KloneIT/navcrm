<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Draft           = 'draft';
    case Sent            = 'sent';
    case Accepted        = 'accepted';
    case Rejected        = 'rejected';
    case Expired         = 'expired';
    case PendingApproval = 'pending_approval';
    case Approved        = 'approved';

    public function label(): string
    {
        return match($this) {
            self::Draft           => 'Draft',
            self::Sent            => 'Sent',
            self::Accepted        => 'Accepted',
            self::Rejected        => 'Rejected',
            self::Expired         => 'Expired',
            self::PendingApproval => 'Pending Approval',
            self::Approved        => 'Approved',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft           => 'secondary',
            self::Sent            => 'info',
            self::Accepted        => 'success',
            self::Rejected        => 'danger',
            self::Expired         => 'warning',
            self::PendingApproval => 'warning',
            self::Approved        => 'success',
        };
    }
}
