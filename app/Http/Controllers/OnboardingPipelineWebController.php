<?php

namespace App\Http\Controllers;

use App\Enums\OnboardingStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\OnboardingPipeline;
use App\Models\OnboardingStep;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingPipelineWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = OnboardingPipeline::with(['account', 'assignee', 'steps']);

        $query->search($request->get('search'), ['name', 'pipeline_number']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($assignee = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignee);
        }

        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        $query->filterDateRange($request->get('due_from'), $request->get('due_to'), 'due_date');

        $pipelines = $query->latest()->paginate(25)->withQueryString();

        $statuses = OnboardingStatus::cases();
        $assignees = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total'       => OnboardingPipeline::count(),
            'in_progress' => OnboardingPipeline::where('status', 'in_progress')->count(),
            'completed'   => OnboardingPipeline::where('status', 'completed')->count(),
            'overdue'     => OnboardingPipeline::whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('success.onboarding.index', compact(
            'pipelines', 'statuses', 'assignees', 'accounts', 'stats'
        ));
    }

    public function create(): View
    {
        $pipeline = null;
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $assignees = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('success.onboarding.create', compact('pipeline', 'accounts', 'contacts', 'assignees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePipeline($request);

        $validated['created_by'] = auth()->id();
        $validated['assigned_to'] = $validated['assigned_to'] ?? auth()->id();
        $validated['pipeline_number'] = $this->generatePipelineNumber();

        $pipeline = OnboardingPipeline::create($validated);

        // Create inline steps
        if ($request->has('steps')) {
            foreach ($request->input('steps', []) as $i => $stepData) {
                if (empty($stepData['title'])) {
                    continue;
                }
                $pipeline->steps()->create([
                    'title'       => $stepData['title'],
                    'description' => $stepData['description'] ?? null,
                    'due_date'    => $stepData['due_date'] ?? null,
                    'sort_order'  => $i,
                ]);
            }
        }

        return redirect()->route('success.onboarding.show', $pipeline)
            ->with('success', 'Onboarding pipeline created successfully.');
    }

    public function show(OnboardingPipeline $onboarding): View
    {
        $onboarding->load([
            'account', 'contact', 'assignee', 'creator',
            'steps.completedByUser',
        ]);

        return view('success.onboarding.show', ['pipeline' => $onboarding]);
    }

    public function edit(OnboardingPipeline $onboarding): View
    {
        $pipeline = $onboarding;
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $assignees = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('success.onboarding.create', compact('pipeline', 'accounts', 'contacts', 'assignees'));
    }

    public function update(Request $request, OnboardingPipeline $onboarding): RedirectResponse
    {
        $validated = $this->validatePipeline($request);

        if ($validated['status'] === 'in_progress' && ! $onboarding->started_at) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'completed' && ! $onboarding->completed_at) {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $onboarding->update($validated);

        return redirect()->route('success.onboarding.show', $onboarding)
            ->with('success', 'Onboarding pipeline updated successfully.');
    }

    public function destroy(OnboardingPipeline $onboarding): RedirectResponse
    {
        $onboarding->delete();

        return redirect()->route('success.onboarding.index')
            ->with('success', 'Onboarding pipeline deleted successfully.');
    }

    // ── Steps ────────────────────────────────────────────────────────────────

    public function storeStep(Request $request, OnboardingPipeline $onboarding): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['nullable', 'date'],
        ]);

        $validated['sort_order'] = $onboarding->steps()->max('sort_order') + 1;

        $onboarding->steps()->create($validated);

        return redirect()->route('success.onboarding.show', $onboarding)
            ->with('success', 'Step added successfully.');
    }

    public function toggleStep(OnboardingPipeline $onboarding, OnboardingStep $step): RedirectResponse
    {
        abort_unless($step->onboarding_pipeline_id === $onboarding->id, 404);

        if ($step->is_completed) {
            // Uncheck
            $step->update([
                'is_completed' => false,
                'completed_at' => null,
                'completed_by' => null,
            ]);
        } else {
            // Check
            $step->update([
                'is_completed' => true,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);
        }

        // Auto-update pipeline status based on step completion
        $onboarding->load('steps');
        $allDone = $onboarding->steps->every('is_completed', true);
        $anyDone = $onboarding->steps->contains('is_completed', true);

        if ($allDone && $onboarding->steps->count() > 0) {
            $onboarding->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        } elseif ($anyDone && $onboarding->status->value === 'not_started') {
            $onboarding->update([
                'status'     => 'in_progress',
                'started_at' => $onboarding->started_at ?? now(),
            ]);
        } elseif (! $allDone && $onboarding->status->value === 'completed') {
            $onboarding->update([
                'status'       => 'in_progress',
                'completed_at' => null,
            ]);
        }

        return redirect()->route('success.onboarding.show', $onboarding)
            ->with('success', 'Step updated.');
    }

    public function destroyStep(OnboardingPipeline $onboarding, OnboardingStep $step): RedirectResponse
    {
        abort_unless($step->onboarding_pipeline_id === $onboarding->id, 404);

        $step->delete();

        return redirect()->route('success.onboarding.show', $onboarding)
            ->with('success', 'Step removed.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function validatePipeline(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'string', 'in:not_started,in_progress,completed,cancelled'],
            'account_id'  => ['required', 'integer', 'exists:accounts,id'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
        ]);
    }

    private function generatePipelineNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = OnboardingPipeline::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('pipeline_number');

        $number = 1;
        if ($last && preg_match('/OB-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'OB-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
