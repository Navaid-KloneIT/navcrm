<?php

namespace App\Enums;

enum EmailSource: string
{
    case Gmail      = 'gmail';
    case Outlook    = 'outlook';
    case BccDropbox = 'bcc_dropbox';
    case Manual     = 'manual';

    public function label(): string
    {
        return match($this) {
            self::Gmail      => 'Gmail',
            self::Outlook    => 'Outlook',
            self::BccDropbox => 'BCC Dropbox',
            self::Manual     => 'Manual',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Gmail      => 'bi-google',
            self::Outlook    => 'bi-microsoft',
            self::BccDropbox => 'bi-inbox-fill',
            self::Manual     => 'bi-pencil',
        };
    }
}
