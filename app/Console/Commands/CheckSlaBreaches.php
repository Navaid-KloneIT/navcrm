<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\AutomationEngine;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    protected $signature   = 'workflow:check-sla';
    protected $description = 'Detect newly SLA-breached tickets and fire automation workflows';

    public function handle(): int
    {
        $breached = Ticket::query()
            ->whereNotNull('sla_due_at')
            ->whereNull('sla_breached_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
            ->get();

        $count = 0;

        foreach ($breached as $ticket) {
            // Mark as breached without triggering further observer loops
            $ticket->updateQuietly(['sla_breached_at' => now()]);

            // Fire automation engine
            AutomationEngine::fire('ticket_sla_breached', $ticket);

            $count++;
        }

        $this->info("Processed {$count} SLA breach(es).");

        return self::SUCCESS;
    }
}
