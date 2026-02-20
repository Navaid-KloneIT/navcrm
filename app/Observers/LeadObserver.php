<?php

namespace App\Observers;

use App\Models\Lead;
use App\Services\AutomationEngine;

class LeadObserver
{
    public function updated(Lead $lead): void
    {
        if ($lead->wasChanged('status')) {
            AutomationEngine::fire('lead_status_changed', $lead);
        }
    }
}
