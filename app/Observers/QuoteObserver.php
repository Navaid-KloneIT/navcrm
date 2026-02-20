<?php

namespace App\Observers;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Services\AutomationEngine;

class QuoteObserver
{
    public function saving(Quote $quote): void
    {
        // Check discount threshold across active workflows for this tenant
        if ($quote->isDirty('discount_value') && $quote->discount_value > 0) {
            $threshold = $this->getDiscountThreshold($quote->tenant_id);

            if ($quote->discount_value > $threshold) {
                $quote->approval_required = true;
                // Only lock if currently draft or sent (not already approved/pending)
                if (in_array($quote->status, [QuoteStatus::Draft, QuoteStatus::Sent])) {
                    $quote->status = QuoteStatus::PendingApproval;
                }
            }
        }
    }

    public function updated(Quote $quote): void
    {
        if ($quote->wasChanged('discount_value') && $quote->approval_required) {
            AutomationEngine::fire('quote_discount_exceeded', $quote);
        }
    }

    private function getDiscountThreshold(int $tenantId): float
    {
        // Look for a workflow with this trigger to get the configured threshold
        $workflow = \App\Models\Workflow::where('tenant_id', $tenantId)
            ->where('trigger_event', 'quote_discount_exceeded')
            ->where('is_active', true)
            ->first();

        return $workflow?->trigger_config['discount_threshold'] ?? 10.0;
    }
}
