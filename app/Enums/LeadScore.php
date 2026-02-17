<?php

namespace App\Enums;

enum LeadScore: string
{
    case Hot = 'hot';
    case Warm = 'warm';
    case Cold = 'cold';
}
