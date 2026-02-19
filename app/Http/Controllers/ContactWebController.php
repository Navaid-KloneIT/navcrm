<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contact::with(['accounts', 'tags', 'owner']);

        $query->search($request->get('search'), ['first_name', 'last_name', 'email', 'phone', 'job_title']);
        $query->filterOwner($request->get('owner_id'));
        $query->filterDateRange($request->get('date_from'), $request->get('date_to'));

        if ($accountId = $request->get('account_id')) {
            $query->whereHas('accounts', fn ($q) => $q->where('accounts.id', $accountId));
        }

        if ($tag = $request->get('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('name', 'like', "%{$tag}%"));
        }

        $contacts = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'     => Contact::count(),
            'thisMonth' => Contact::whereMonth('created_at', now()->month)->count(),
        ];

        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        return view('contacts.index', compact('contacts', 'stats', 'owners', 'accounts'));
    }

    public function create(): View
    {
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $tags     = Tag::orderBy('name')->get(['id', 'name']);
        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $contact  = null;

        return view('contacts.create', compact('contact', 'accounts', 'tags', 'owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'mobile'        => ['nullable', 'string', 'max:50'],
            'job_title'     => ['nullable', 'string', 'max:150'],
            'department'    => ['nullable', 'string', 'max:100'],
            'description'   => ['nullable', 'string'],
            'linkedin_url'  => ['nullable', 'url', 'max:255'],
            'twitter_handle'=> ['nullable', 'string', 'max:100'],
            'facebook_url'  => ['nullable', 'url', 'max:255'],
            'address_line_1'=> ['nullable', 'string', 'max:255'],
            'address_line_2'=> ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:100'],
            'source'        => ['nullable', 'string', 'max:50'],
            'owner_id'      => ['nullable', 'integer', 'exists:users,id'],
            'account_id'    => ['nullable', 'integer', 'exists:accounts,id'],
            'tags'          => ['nullable', 'array'],
            'tags.*'        => ['integer', 'exists:tags,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $accountId = $validated['account_id'] ?? null;
        unset($validated['account_id'], $validated['tags']);

        $contact = Contact::create($validated);

        if ($accountId) {
            $contact->accounts()->attach($accountId, ['is_primary' => true]);
        }

        if ($request->filled('tags')) {
            $contact->tags()->sync($request->input('tags'));
        }

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully.');
    }

    public function show(Contact $contact): View
    {
        $contact->load(['accounts', 'tags', 'owner', 'activities.user', 'notes.user', 'relatedContacts']);

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact): View
    {
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $tags     = Tag::orderBy('name')->get(['id', 'name']);
        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('contacts.create', compact('contact', 'accounts', 'tags', 'owners'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'mobile'        => ['nullable', 'string', 'max:50'],
            'job_title'     => ['nullable', 'string', 'max:150'],
            'department'    => ['nullable', 'string', 'max:100'],
            'description'   => ['nullable', 'string'],
            'linkedin_url'  => ['nullable', 'url', 'max:255'],
            'twitter_handle'=> ['nullable', 'string', 'max:100'],
            'facebook_url'  => ['nullable', 'url', 'max:255'],
            'address_line_1'=> ['nullable', 'string', 'max:255'],
            'address_line_2'=> ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:100'],
            'source'        => ['nullable', 'string', 'max:50'],
            'owner_id'      => ['nullable', 'integer', 'exists:users,id'],
            'tags'          => ['nullable', 'array'],
            'tags.*'        => ['integer', 'exists:tags,id'],
        ]);

        unset($validated['tags']);

        $contact->update($validated);

        if ($request->has('tags')) {
            $contact->tags()->sync($request->input('tags', []));
        }

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}
