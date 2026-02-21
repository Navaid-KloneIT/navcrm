<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\HealthScore;
use App\Models\OnboardingPipeline;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Services\HealthScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthScoreWebController extends Controller
{
    public function __construct(
        private HealthScoreService $healthScoreService
    ) {}

    /**
     * CS Dashboard — unified overview.
     */
    public function dashboard(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Active onboarding count
        $activeOnboarding = OnboardingPipeline::where('status', 'in_progress')->count();

        // Average health score (latest per account)
        $latestScores = HealthScore::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('account_id')
            ->pluck('id');
        $avgHealth = HealthScore::whereIn('id', $latestScores)->avg('overall_score');
        $avgHealth = $avgHealth !== null ? round($avgHealth) : null;

        // NPS — average across active NPS surveys
        $npsSurveys = Survey::where('type', 'nps')->where('status', 'active')->pluck('id');
        $npsScore = null;
        if ($npsSurveys->isNotEmpty()) {
            $total      = SurveyResponse::whereIn('survey_id', $npsSurveys)->count();
            $promoters  = SurveyResponse::whereIn('survey_id', $npsSurveys)->where('score', '>=', 9)->count();
            $detractors = SurveyResponse::whereIn('survey_id', $npsSurveys)->where('score', '<=', 6)->count();
            $npsScore   = $total > 0 ? (int) round((($promoters - $detractors) / $total) * 100) : null;
        }

        // CSAT average
        $csatAvg = Survey::where('type', 'csat')
            ->where('status', 'active')
            ->get()
            ->avg(fn ($s) => $s->average_score);
        $csatAvg = $csatAvg !== null ? round($csatAvg, 1) : null;

        // At-risk accounts
        $atRiskAccounts = Account::with('latestHealthScore')
            ->whereHas('latestHealthScore', function ($q) {
                $q->where('overall_score', '<', 40);
            })
            ->limit(10)
            ->get();

        // Active onboarding pipelines
        $activePipelines = OnboardingPipeline::with(['account', 'assignee', 'steps'])
            ->where('status', 'in_progress')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Recent survey responses
        $recentResponses = SurveyResponse::with(['survey', 'contact', 'account'])
            ->latest('responded_at')
            ->limit(10)
            ->get();

        return view('success.dashboard', compact(
            'activeOnboarding', 'avgHealth', 'npsScore', 'csatAvg',
            'atRiskAccounts', 'activePipelines', 'recentResponses'
        ));
    }

    /**
     * Health Scores index — all accounts with their latest score.
     */
    public function index(Request $request): View
    {
        $query = Account::with('latestHealthScore');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($range = $request->get('health_range')) {
            $query->whereHas('latestHealthScore', function ($q) use ($range) {
                match ($range) {
                    'healthy'  => $q->where('overall_score', '>=', 70),
                    'at_risk'  => $q->where('overall_score', '>=', 40)->where('overall_score', '<', 70),
                    'critical' => $q->where('overall_score', '<', 40),
                    default    => null,
                };
            });
        }

        $accounts = $query->orderBy('name')->paginate(25)->withQueryString();

        // Stats from latest scores
        $allLatest = HealthScore::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('account_id')
            ->pluck('id');
        $latestScores = HealthScore::whereIn('id', $allLatest)->get();

        $stats = [
            'avg_score' => $latestScores->avg('overall_score') !== null ? round($latestScores->avg('overall_score')) : 0,
            'healthy'   => $latestScores->where('overall_score', '>=', 70)->count(),
            'at_risk'   => $latestScores->where('overall_score', '>=', 40)->where('overall_score', '<', 70)->count(),
            'critical'  => $latestScores->where('overall_score', '<', 40)->count(),
        ];

        return view('success.health-scores.index', compact('accounts', 'stats'));
    }

    /**
     * Health Score detail for a specific account.
     */
    public function show(Account $account): View
    {
        $account->load('latestHealthScore');

        $history = HealthScore::where('account_id', $account->id)
            ->orderByDesc('calculated_at')
            ->limit(20)
            ->get();

        return view('success.health-scores.show', compact('account', 'history'));
    }

    /**
     * Recalculate health score for a single account.
     */
    public function recalculate(Account $account): RedirectResponse
    {
        $this->healthScoreService->calculateForAccount($account);

        return redirect()->route('success.health-scores.show', $account)
            ->with('success', 'Health score recalculated successfully.');
    }

    /**
     * Recalculate health scores for all accounts.
     */
    public function recalculateAll(): RedirectResponse
    {
        $count = $this->healthScoreService->calculateForAllAccounts(auth()->user()->tenant_id);

        return redirect()->route('success.health-scores.index')
            ->with('success', "Health scores recalculated for {$count} accounts.");
    }
}
