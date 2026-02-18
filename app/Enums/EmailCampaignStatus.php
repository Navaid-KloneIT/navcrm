<?php

namespace App\Enums;

enum EmailCampaignStatus: string
{
    case Draft     = 'draft';
    case Scheduled = 'scheduled';
    case Sending   = 'sending';
    case Sent      = 'sent';
    case Paused    = 'paused';
}
