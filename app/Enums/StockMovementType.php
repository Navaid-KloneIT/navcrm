<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PurchaseIn = 'purchase_in';
    case SaleOut    = 'sale_out';
    case Adjustment = 'adjustment';
    case ReturnIn   = 'return_in';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseIn => 'Purchase In',
            self::SaleOut    => 'Sale Out',
            self::Adjustment => 'Adjustment',
            self::ReturnIn   => 'Return In',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PurchaseIn => 'success',
            self::SaleOut    => 'danger',
            self::Adjustment => 'warning',
            self::ReturnIn   => 'info',
        };
    }
}
