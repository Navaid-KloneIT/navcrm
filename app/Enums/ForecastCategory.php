<?php

namespace App\Enums;

enum ForecastCategory: string
{
    case Pipeline = 'pipeline';
    case BestCase = 'best_case';
    case Commit = 'commit';
    case Closed = 'closed';
}
