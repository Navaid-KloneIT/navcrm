<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailLog\StoreEmailLogRequest;
use App\Http\Requests\EmailLog\UpdateEmailLogRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmailLog::with(['user', 'emailable']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('to_email', 'like', "%{$search}%");
            });
        }

        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $emails = $query->orderBy('sent_at', 'desc')->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $users  = User::orderBy('name')->get(['id', 'name']);

        return view('activity.emails.index', compact('emails', 'users'));
    }

    public function create(): View
    {
        $email    = null;
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $leads    = Lead::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $accounts = Account::orderBy('name')->get(['id', 'name', 'email']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('activity.emails.create', compact('email', 'contacts', 'leads', 'accounts', 'users'));
    }

    public function store(StoreEmailLogRequest $request): RedirectResponse
    {
        $email = EmailLog::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
        ]);

        return redirect()->route('activity.emails.show', $email)
            ->with('success', 'Email logged successfully.');
    }

    public function show(EmailLog $emailLog): View
    {
        $emailLog->load(['user', 'emailable']);

        return view('activity.emails.show', ['email' => $emailLog]);
    }

    public function edit(EmailLog $emailLog): View
    {
        $email    = $emailLog;
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $leads    = Lead::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $accounts = Account::orderBy('name')->get(['id', 'name', 'email']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('activity.emails.create', compact('email', 'contacts', 'leads', 'accounts', 'users'));
    }

    public function update(UpdateEmailLogRequest $request, EmailLog $emailLog): RedirectResponse
    {
        $emailLog->update($request->validated());

        return redirect()->route('activity.emails.show', $emailLog)
            ->with('success', 'Email log updated successfully.');
    }

    public function destroy(EmailLog $emailLog): RedirectResponse
    {
        $emailLog->delete();

        return redirect()->route('activity.emails.index')
            ->with('success', 'Email log deleted successfully.');
    }
}
