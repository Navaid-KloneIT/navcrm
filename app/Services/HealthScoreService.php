<?php

namespace App\Services;

use App\Models\Account;
use App\Models\HealthScore;
use App\Models\Ticket;
use App\Models\Invoice;
use App\Models\Activity;

class HealthScoreService
{
    /**
     * Calculate and store a health score for a single account.
     */
    public function calculateForAccount(Account $account): HealthScore
    {
        $loginResult   = $this->calculateLoginScore($account);
        $ticketResult  = $this->calculateTicketScore($account);
        $paymentResult = $this->calculatePaymentScore($account);

        $overall = $this->calculateOverall(
            $loginResult['score'],
            $ticketResult['score'],
            $paymentResult['score']
        );

        return HealthScore::create([
            'tenant_id'     => $account->tenant_id,
            'account_id'    => $account->id,
            'overall_score'  => $overall,
            'login_score'    => $loginResult['score'],
            'ticket_score'   => $ticketResult['score'],
            'payment_score'  => $paymentResult['score'],
            'factors'        => [
                'login'   => $loginResult['factors'],
                'tickets' => $ticketResult['factors'],
                'payments' => $paymentResult['factors'],
            ],
            'calculated_at' => now(),
        ]);
    }

    /**
     * Recalculate health scores for all accounts in a tenant.
     */
    public function calculateForAllAccounts(int $tenantId): int
    {
        $accounts = Account::where('tenant_id', $tenantId)->get();
        $count = 0;

        foreach ($accounts as $account) {
            $this->calculateForAccount($account);
            $count++;
        }

        return $count;
    }

    /**
     * Engagement score (0-100) based on activity count in the last 30 days.
     * Uses activities (calls, emails, tasks) linked to the account as a proxy.
     */
    private function calculateLoginScore(Account $account): array
    {
        $activityCount = Activity::where('activitable_type', Account::class)
            ->where('activitable_id', $account->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $score = match (true) {
            $activityCount >= 10 => 100,
            $activityCount >= 7  => 80,
            $activityCount >= 4  => 60,
            $activityCount >= 1  => 40,
            default              => 10,
        };

        return [
            'score'   => $score,
            'factors' => [
                'activity_count_30d' => $activityCount,
                'detail'             => "{$activityCount} activities in last 30 days",
            ],
        ];
    }

    /**
     * Ticket score (0-100) based on open tickets in the last 90 days.
     * Fewer open tickets = healthier. SLA breaches penalize further.
     */
    private function calculateTicketScore(Account $account): array
    {
        $openTickets = Ticket::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $breachedCount = Ticket::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->whereNotNull('sla_breached_at')
            ->count();

        $score = match (true) {
            $openTickets === 0   => 100,
            $openTickets === 1   => 85,
            $openTickets <= 3    => 65,
            $openTickets <= 5    => 40,
            default              => 15,
        };

        if ($breachedCount > 0) {
            $score = max(0, $score - 20);
        }

        return [
            'score'   => $score,
            'factors' => [
                'open_count_90d' => $openTickets,
                'breached_count' => $breachedCount,
                'detail'         => "{$openTickets} open tickets in last 90 days, {$breachedCount} SLA breaches",
            ],
        ];
    }

    /**
     * Payment score (0-100) based on invoice payment timeliness in the last 6 months.
     */
    private function calculatePaymentScore(Account $account): array
    {
        $invoices = Invoice::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->get();

        $total = $invoices->count();

        if ($total === 0) {
            return [
                'score'   => 80,
                'factors' => [
                    'total_invoices_6m'    => 0,
                    'paid_on_time'         => 0,
                    'overdue_count'        => 0,
                    'outstanding_overdue'  => 0,
                    'detail'               => 'No invoices in last 6 months',
                ],
            ];
        }

        $paidOnTime = $invoices->filter(function ($inv) {
            return in_array($inv->status->value ?? $inv->status, ['paid']) && !$inv->overdue_at;
        })->count();

        $overdueCount = $invoices->filter(function ($inv) {
            return in_array($inv->status->value ?? $inv->status, ['overdue']);
        })->count();

        $outstandingOverdue = $invoices->filter(function ($inv) {
            $status = $inv->status->value ?? $inv->status;
            return $status === 'overdue';
        })->count();

        $ratio = $paidOnTime / $total;
        $score = (int) round($ratio * 100);

        // Penalize outstanding overdue invoices
        if ($outstandingOverdue > 0) {
            $score = max(0, $score - ($outstandingOverdue * 15));
        }

        $score = max(0, min(100, $score));

        return [
            'score'   => $score,
            'factors' => [
                'total_invoices_6m'    => $total,
                'paid_on_time'         => $paidOnTime,
                'overdue_count'        => $overdueCount,
                'outstanding_overdue'  => $outstandingOverdue,
                'detail'               => "{$paidOnTime} of {$total} invoices paid on time (" . round($ratio * 100) . "%)",
            ],
        ];
    }

    /**
     * Overall score: weighted average.
     * Login/engagement: 30%, Ticket health: 35%, Payment health: 35%
     */
    private function calculateOverall(int $login, int $ticket, int $payment): int
    {
        return (int) round($login * 0.30 + $ticket * 0.35 + $payment * 0.35);
    }
}
