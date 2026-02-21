<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case Draft  = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft  => 'Draft',
            self::Active => 'Active',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft  => 'secondary',
            self::Active => 'primary',
            self::Closed => 'success',
        };
    }
}
