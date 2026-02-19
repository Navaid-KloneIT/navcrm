<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low      = 'low';
    case Medium   = 'medium';
    case High     = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match($this) {
            self::Low      => 'Low',
            self::Medium   => 'Medium',
            self::High     => 'High',
            self::Critical => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low      => 'secondary',
            self::Medium   => 'info',
            self::High     => 'warning',
            self::Critical => 'danger',
        };
    }

    public function slaHours(): int
    {
        return match($this) {
            self::Low      => 72,
            self::Medium   => 24,
            self::High     => 8,
            self::Critical => 4,
        };
    }
}
