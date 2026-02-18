<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Campaign::with(['owner']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $campaigns = $query->latest()->paginate(25)->withQueryString();

        $statusCounts = Campaign::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('marketing.campaigns.index', compact('campaigns', 'statusCounts'));
    }

    public function create(): View
    {
        $owners = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.campaigns.create', compact('owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'string'],
            'status'          => ['nullable', 'string'],
            'description'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'planned_budget'  => ['nullable', 'numeric', 'min:0'],
            'actual_budget'   => ['nullable', 'numeric', 'min:0'],
            'target_revenue'  => ['nullable', 'numeric', 'min:0'],
            'actual_revenue'  => ['nullable', 'numeric', 'min:0'],
            'owner_id'        => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $campaign = Campaign::create($validated);

        return redirect()->route('marketing.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['owner', 'creator', 'targetLists', 'emailCampaigns']);

        return view('marketing.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign): View
    {
        $owners = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.campaigns.create', compact('campaign', 'owners'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'string'],
            'status'          => ['nullable', 'string'],
            'description'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'planned_budget'  => ['nullable', 'numeric', 'min:0'],
            'actual_budget'   => ['nullable', 'numeric', 'min:0'],
            'target_revenue'  => ['nullable', 'numeric', 'min:0'],
            'actual_revenue'  => ['nullable', 'numeric', 'min:0'],
            'owner_id'        => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $campaign->update($validated);

        return redirect()->route('marketing.campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('marketing.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
}
