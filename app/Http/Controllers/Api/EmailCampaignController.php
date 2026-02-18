<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EmailCampaign::with(['campaign', 'template', 'owner']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($campaignId = $request->get('campaign_id')) {
            $query->where('campaign_id', $campaignId);
        }

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $emailCampaigns = $query->paginate($request->get('per_page', 25));

        return response()->json($emailCampaigns);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'type'               => ['required', 'in:single,drip,ab_test'],
            'campaign_id'        => ['nullable', 'exists:campaigns,id'],
            'email_template_id'  => ['nullable', 'exists:email_templates,id'],
            'from_name'          => ['nullable', 'string', 'max:150'],
            'from_email'         => ['nullable', 'email', 'max:255'],
            'subject'            => ['nullable', 'string', 'max:255'],
            'subject_a'          => ['nullable', 'string', 'max:255'],
            'subject_b'          => ['nullable', 'string', 'max:255'],
            'scheduled_at'       => ['nullable', 'date'],
            'owner_id'           => ['nullable', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $emailCampaign = EmailCampaign::create($validated);

        return response()->json($emailCampaign->load(['campaign', 'template']), 201);
    }

    public function show(EmailCampaign $emailCampaign): JsonResponse
    {
        return response()->json(
            $emailCampaign->load(['campaign', 'template', 'owner', 'creator'])
        );
    }

    public function update(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['sometimes', 'required', 'string', 'max:255'],
            'type'               => ['sometimes', 'in:single,drip,ab_test'],
            'campaign_id'        => ['nullable', 'exists:campaigns,id'],
            'email_template_id'  => ['nullable', 'exists:email_templates,id'],
            'from_name'          => ['nullable', 'string', 'max:150'],
            'from_email'         => ['nullable', 'email', 'max:255'],
            'subject'            => ['nullable', 'string', 'max:255'],
            'subject_a'          => ['nullable', 'string', 'max:255'],
            'subject_b'          => ['nullable', 'string', 'max:255'],
            'scheduled_at'       => ['nullable', 'date'],
            'owner_id'           => ['nullable', 'exists:users,id'],
        ]);

        $emailCampaign->update($validated);

        return response()->json($emailCampaign->fresh(['campaign', 'template']));
    }

    public function destroy(EmailCampaign $emailCampaign): JsonResponse
    {
        $emailCampaign->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,scheduled,sending,sent,paused'],
        ]);

        $emailCampaign->update(['status' => $validated['status']]);

        if ($validated['status'] === EmailCampaignStatus::Sent->value) {
            $emailCampaign->update(['sent_at' => now()]);
        }

        return response()->json($emailCampaign->fresh());
    }
}
