<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CreditCard   = 'credit_card';
    case Stripe       = 'stripe';
    case PayPal       = 'paypal';
    case Razorpay     = 'razorpay';
    case Cash         = 'cash';
    case Cheque       = 'cheque';

    public function label(): string
    {
        return match($this) {
            self::BankTransfer => 'Bank Transfer',
            self::CreditCard   => 'Credit Card',
            self::Stripe       => 'Stripe',
            self::PayPal       => 'PayPal',
            self::Razorpay     => 'Razorpay',
            self::Cash         => 'Cash',
            self::Cheque       => 'Cheque',
        };
    }
}
