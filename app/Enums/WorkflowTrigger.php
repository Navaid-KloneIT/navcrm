<?php

namespace App\Enums;

enum WorkflowTrigger: string
{
    case LeadStatusChanged       = 'lead_status_changed';
    case OpportunityStageChanged = 'opportunity_stage_changed';
    case QuoteDiscountExceeded   = 'quote_discount_exceeded';
    case TicketSlaBreached       = 'ticket_sla_breached';

    public function label(): string
    {
        return match($this) {
            self::LeadStatusChanged       => 'Lead Status Changed',
            self::OpportunityStageChanged => 'Opportunity Stage Changed',
            self::QuoteDiscountExceeded   => 'Quote Discount Exceeded',
            self::TicketSlaBreached       => 'Ticket SLA Breached',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::LeadStatusChanged       => 'Fires when a lead\'s status is updated',
            self::OpportunityStageChanged => 'Fires when an opportunity moves to a new pipeline stage',
            self::QuoteDiscountExceeded   => 'Fires when a quote\'s discount exceeds a configured threshold',
            self::TicketSlaBreached       => 'Fires when a support ticket breaches its SLA deadline',
        };
    }

    public function entityType(): string
    {
        return match($this) {
            self::LeadStatusChanged       => 'lead',
            self::OpportunityStageChanged => 'opportunity',
            self::QuoteDiscountExceeded   => 'quote',
            self::TicketSlaBreached       => 'ticket',
        };
    }
}
