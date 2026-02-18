<?php

namespace App\Enums;

enum CampaignType: string
{
    case Email      = 'email';
    case Webinar    = 'webinar';
    case Event      = 'event';
    case DigitalAds = 'digital_ads';
    case DirectMail = 'direct_mail';
}
