<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Travel        = 'travel';
    case Meals         = 'meals';
    case Software      = 'software';
    case Entertainment = 'entertainment';
    case Accommodation = 'accommodation';
    case Other         = 'other';

    public function label(): string
    {
        return match($this) {
            self::Travel        => 'Travel',
            self::Meals         => 'Meals',
            self::Software      => 'Software',
            self::Entertainment => 'Entertainment',
            self::Accommodation => 'Accommodation',
            self::Other         => 'Other',
        };
    }
}
