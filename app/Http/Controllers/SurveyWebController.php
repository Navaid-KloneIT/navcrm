<?php

namespace App\Http\Controllers;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\Account;
use App\Models\Survey;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SurveyWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Survey::with(['account', 'ticket', 'creator', 'responses']);

        $query->search($request->get('search'), ['name', 'survey_number']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $surveys = $query->latest()->paginate(25)->withQueryString();

        $types    = SurveyType::cases();
        $statuses = SurveyStatus::cases();

        // Stats
        $npsSurveys = Survey::where('type', 'nps')->where('status', 'active')->get();
        $csatSurveys = Survey::where('type', 'csat')->where('status', 'active')->get();

        $stats = [
            'total'    => Survey::count(),
            'active'   => Survey::where('status', 'active')->count(),
            'avg_nps'  => $npsSurveys->count() > 0
                ? round($npsSurveys->avg(fn ($s) => $s->nps_score ?? 0))
                : null,
            'avg_csat' => $csatSurveys->count() > 0
                ? round($csatSurveys->avg(fn ($s) => $s->average_score ?? 0), 1)
                : null,
        ];

        return view('success.surveys.index', compact('surveys', 'types', 'statuses', 'stats'));
    }

    public function create(): View
    {
        $survey   = null;
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $tickets  = Ticket::whereIn('status', ['resolved', 'closed'])
            ->orderByDesc('resolved_at')
            ->limit(100)
            ->get(['id', 'ticket_number', 'subject']);

        return view('success.surveys.create', compact('survey', 'accounts', 'tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSurvey($request);

        $validated['created_by']    = auth()->id();
        $validated['survey_number'] = $this->generateSurveyNumber();
        $validated['token']         = Str::random(64);

        $survey = Survey::create($validated);

        return redirect()->route('success.surveys.show', $survey)
            ->with('success', 'Survey created successfully.');
    }

    public function show(Survey $survey): View
    {
        $survey->load(['account', 'ticket', 'creator', 'responses.contact', 'responses.account']);

        $responses = $survey->responses()->with(['contact', 'account'])
            ->orderByDesc('responded_at')
            ->paginate(25);

        // NPS breakdown
        $npsData = null;
        if ($survey->type === SurveyType::Nps) {
            $total      = $survey->responses->count();
            $promoters  = $survey->responses->where('score', '>=', 9)->count();
            $passives   = $survey->responses->whereBetween('score', [7, 8])->count();
            $detractors = $survey->responses->where('score', '<=', 6)->count();

            $npsData = [
                'score'      => $total > 0 ? (int) round((($promoters - $detractors) / $total) * 100) : 0,
                'promoters'  => $promoters,
                'passives'   => $passives,
                'detractors' => $detractors,
                'total'      => $total,
            ];
        }

        // CSAT breakdown
        $csatData = null;
        if ($survey->type === SurveyType::Csat) {
            $total = $survey->responses->count();
            $distribution = [];
            for ($i = 1; $i <= 10; $i++) {
                $distribution[$i] = $survey->responses->where('score', $i)->count();
            }
            $csatData = [
                'average'      => $total > 0 ? round($survey->responses->avg('score'), 1) : 0,
                'total'        => $total,
                'distribution' => $distribution,
            ];
        }

        $publicUrl = route('survey.respond', $survey->token);

        return view('success.surveys.show', compact('survey', 'responses', 'npsData', 'csatData', 'publicUrl'));
    }

    public function edit(Survey $survey): View
    {
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $tickets  = Ticket::whereIn('status', ['resolved', 'closed'])
            ->orderByDesc('resolved_at')
            ->limit(100)
            ->get(['id', 'ticket_number', 'subject']);

        return view('success.surveys.create', compact('survey', 'accounts', 'tickets'));
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $validated = $this->validateSurvey($request);

        $survey->update($validated);

        return redirect()->route('success.surveys.show', $survey)
            ->with('success', 'Survey updated successfully.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();

        return redirect()->route('success.surveys.index')
            ->with('success', 'Survey deleted successfully.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validateSurvey(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', 'string', 'in:nps,csat'],
            'status'      => ['required', 'string', 'in:draft,active,closed'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'ticket_id'   => ['nullable', 'integer', 'exists:tickets,id'],
        ]);
    }

    private function generateSurveyNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = Survey::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('survey_number');

        $number = 1;
        if ($last && preg_match('/SV-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'SV-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
