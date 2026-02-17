<?php

namespace App\Enums;

enum OpportunitySource: string
{
    case Web = 'web';
    case Referral = 'referral';
    case Partner = 'partner';
    case Outbound = 'outbound';
    case Inbound = 'inbound';
    case Other = 'other';
}
