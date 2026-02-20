<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Quote;
use App\Observers\LeadObserver;
use App\Observers\OpportunityObserver;
use App\Observers\QuoteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Lead::observe(LeadObserver::class);
        Opportunity::observe(OpportunityObserver::class);
        Quote::observe(QuoteObserver::class);
    }
}
