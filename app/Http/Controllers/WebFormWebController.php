<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\WebForm;
use App\Models\WebFormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebFormWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = WebForm::with(['creator', 'assignedUser']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('active')) {
            $query->where('is_active', (bool) $request->get('active'));
        }

        $forms = $query->latest()->paginate(25)->withQueryString();

        return view('marketing.web-forms.index', compact('forms'));
    }

    public function create(): View
    {
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.web-forms.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'fields'               => ['nullable', 'array'],
            'submit_button_text'   => ['nullable', 'string', 'max:100'],
            'success_message'      => ['nullable', 'string'],
            'redirect_url'         => ['nullable', 'url'],
            'assign_to_user_id'    => ['nullable', 'exists:users,id'],
            'assign_by_geography'  => ['nullable', 'boolean'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $validated['created_by']           = auth()->id();
        $validated['is_active']            = $request->boolean('is_active', true);
        $validated['assign_by_geography']  = $request->boolean('assign_by_geography', false);

        $form = WebForm::create($validated);

        return redirect()->route('marketing.web-forms.show', $form)
            ->with('success', 'Web form created successfully.');
    }

    public function show(WebForm $webForm): View
    {
        $webForm->load(['creator', 'assignedUser']);
        $submissions = $webForm->submissions()->latest()->paginate(20);

        return view('marketing.web-forms.show', compact('webForm', 'submissions'));
    }

    public function edit(WebForm $webForm): View
    {
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.web-forms.create', compact('webForm', 'users'));
    }

    public function update(Request $request, WebForm $webForm): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'fields'               => ['nullable', 'array'],
            'submit_button_text'   => ['nullable', 'string', 'max:100'],
            'success_message'      => ['nullable', 'string'],
            'redirect_url'         => ['nullable', 'url'],
            'assign_to_user_id'    => ['nullable', 'exists:users,id'],
            'assign_by_geography'  => ['nullable', 'boolean'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['assign_by_geography'] = $request->boolean('assign_by_geography', false);

        $webForm->update($validated);

        return redirect()->route('marketing.web-forms.show', $webForm)
            ->with('success', 'Web form updated successfully.');
    }

    public function destroy(WebForm $webForm): RedirectResponse
    {
        $webForm->delete();

        return redirect()->route('marketing.web-forms.index')
            ->with('success', 'Web form deleted successfully.');
    }

    public function convertSubmission(Request $request, WebFormSubmission $submission): RedirectResponse
    {
        if ($submission->is_converted) {
            return back()->with('error', 'This submission has already been converted to a lead.');
        }

        $data = $submission->data;

        $lead = Lead::create([
            'first_name'   => $data['first_name'] ?? 'Unknown',
            'last_name'    => $data['last_name']  ?? 'Lead',
            'email'        => $data['email']      ?? null,
            'phone'        => $data['phone']      ?? null,
            'company_name' => $data['company']    ?? null,
            'source'       => 'Web Form',
            'owner_id'     => $submission->form->assign_to_user_id ?? auth()->id(),
            'created_by'   => auth()->id(),
        ]);

        $submission->update([
            'is_converted' => true,
            'lead_id'      => $lead->id,
        ]);

        $submission->form->increment('total_submissions');

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Submission converted to lead successfully.');
    }
}
