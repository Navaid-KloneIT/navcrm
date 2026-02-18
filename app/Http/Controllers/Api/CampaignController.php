<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignTargetList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::with(['owner']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $campaigns = $query->paginate($request->get('per_page', 25));

        return response()->json($campaigns);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'string'],
            'status'          => ['nullable', 'string'],
            'description'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
            'planned_budget'  => ['nullable', 'numeric', 'min:0'],
            'actual_budget'   => ['nullable', 'numeric', 'min:0'],
            'target_revenue'  => ['nullable', 'numeric', 'min:0'],
            'actual_revenue'  => ['nullable', 'numeric', 'min:0'],
            'owner_id'        => ['nullable', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $campaign = Campaign::create($validated);

        return response()->json($campaign->load('owner'), 201);
    }

    public function show(Campaign $campaign): JsonResponse
    {
        return response()->json(
            $campaign->load(['owner', 'creator', 'targetLists', 'emailCampaigns'])
        );
    }

    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'type'            => ['sometimes', 'required', 'string'],
            'status'          => ['nullable', 'string'],
            'description'     => ['nullable', 'string'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
            'planned_budget'  => ['nullable', 'numeric', 'min:0'],
            'actual_budget'   => ['nullable', 'numeric', 'min:0'],
            'target_revenue'  => ['nullable', 'numeric', 'min:0'],
            'actual_revenue'  => ['nullable', 'numeric', 'min:0'],
            'owner_id'        => ['nullable', 'exists:users,id'],
        ]);

        $campaign->update($validated);

        return response()->json($campaign->fresh('owner'));
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return response()->json(null, 204);
    }

    public function targetLists(Campaign $campaign): JsonResponse
    {
        return response()->json($campaign->targetLists()->withCount(['contacts', 'leads'])->get());
    }

    public function storeTargetList(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $list = $campaign->targetLists()->create($validated);

        return response()->json($list, 201);
    }

    public function updateTargetList(Request $request, CampaignTargetList $targetList): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $targetList->update($validated);

        return response()->json($targetList);
    }

    public function destroyTargetList(CampaignTargetList $targetList): JsonResponse
    {
        $targetList->delete();

        return response()->json(null, 204);
    }

    public function syncContacts(Request $request, CampaignTargetList $targetList): JsonResponse
    {
        $validated = $request->validate([
            'contact_ids'   => ['required', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
        ]);

        $targetList->contacts()->sync($validated['contact_ids']);

        return response()->json(['message' => 'Contacts synced successfully.']);
    }

    public function syncLeads(Request $request, CampaignTargetList $targetList): JsonResponse
    {
        $validated = $request->validate([
            'lead_ids'   => ['required', 'array'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
        ]);

        $targetList->leads()->sync($validated['lead_ids']);

        return response()->json(['message' => 'Leads synced successfully.']);
    }
}
