<?php

namespace App\Enums;

enum SurveyType: string
{
    case Nps  = 'nps';
    case Csat = 'csat';

    public function label(): string
    {
        return match ($this) {
            self::Nps  => 'NPS',
            self::Csat => 'CSAT',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Nps  => 'info',
            self::Csat => 'warning',
        };
    }
}
