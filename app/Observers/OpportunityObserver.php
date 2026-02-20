<?php

namespace App\Observers;

use App\Models\Opportunity;
use App\Services\AutomationEngine;

class OpportunityObserver
{
    public function updated(Opportunity $opportunity): void
    {
        if ($opportunity->wasChanged('pipeline_stage_id')) {
            AutomationEngine::fire('opportunity_stage_changed', $opportunity);
        }
    }
}
