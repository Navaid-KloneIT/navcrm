<?php

namespace App\Http\Controllers;

use App\Enums\EmailCampaignStatus;
use App\Models\Campaign;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailCampaignWebController extends Controller
{
    public function index(Request $request): View
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

        $emailCampaigns = $query->latest()->paginate(25)->withQueryString();

        return view('marketing.email-campaigns.index', compact('emailCampaigns'));
    }

    public function create(): View
    {
        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);
        $templates  = EmailTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $owners     = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.email-campaigns.create', compact('campaigns', 'templates', 'owners'));
    }

    public function store(Request $request): RedirectResponse
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
            'owner_id'           => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $emailCampaign = EmailCampaign::create($validated);

        return redirect()->route('marketing.email-campaigns.show', $emailCampaign)
            ->with('success', 'Email campaign created successfully.');
    }

    public function show(EmailCampaign $emailCampaign): View
    {
        $emailCampaign->load(['campaign', 'template', 'owner', 'creator']);

        return view('marketing.email-campaigns.show', compact('emailCampaign'));
    }

    public function edit(EmailCampaign $emailCampaign): View
    {
        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);
        $templates  = EmailTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $owners     = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.email-campaigns.create', compact('emailCampaign', 'campaigns', 'templates', 'owners'));
    }

    public function update(Request $request, EmailCampaign $emailCampaign): RedirectResponse
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
            'owner_id'           => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $emailCampaign->update($validated);

        return redirect()->route('marketing.email-campaigns.show', $emailCampaign)
            ->with('success', 'Email campaign updated successfully.');
    }

    public function destroy(EmailCampaign $emailCampaign): RedirectResponse
    {
        $emailCampaign->delete();

        return redirect()->route('marketing.email-campaigns.index')
            ->with('success', 'Email campaign deleted successfully.');
    }
}
