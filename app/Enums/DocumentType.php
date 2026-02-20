<?php

namespace App\Enums;

enum DocumentType: string
{
    case NDA      = 'nda';
    case Contract = 'contract';
    case Proposal = 'proposal';
    case SOW      = 'sow';
    case MSA      = 'msa';
    case Other    = 'other';

    public function label(): string
    {
        return match($this) {
            self::NDA      => 'NDA',
            self::Contract => 'Contract',
            self::Proposal => 'Proposal',
            self::SOW      => 'Statement of Work',
            self::MSA      => 'Master Service Agreement',
            self::Other    => 'Other',
        };
    }
}
