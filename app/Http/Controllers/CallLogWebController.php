<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallLog\StoreCallLogRequest;
use App\Http\Requests\CallLog\UpdateCallLogRequest;
use App\Models\Account;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallLogWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = CallLog::with(['user', 'loggable']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $calls = $query->orderBy('called_at', 'desc')->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calls.index', compact('calls', 'users'));
    }

    public function create(): View
    {
        $call     = null;
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone', 'mobile']);
        $leads    = Lead::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']);
        $accounts = Account::orderBy('name')->get(['id', 'name', 'phone']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calls.create', compact('call', 'contacts', 'leads', 'accounts', 'users'));
    }

    public function store(StoreCallLogRequest $request): RedirectResponse
    {
        $call = CallLog::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
        ]);

        return redirect()->route('activity.calls.show', $call)
            ->with('success', 'Call logged successfully.');
    }

    public function show(CallLog $callLog): View
    {
        $callLog->load(['user', 'loggable']);

        return view('activity.calls.show', ['call' => $callLog]);
    }

    public function edit(CallLog $callLog): View
    {
        $call     = $callLog;
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone', 'mobile']);
        $leads    = Lead::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'phone']);
        $accounts = Account::orderBy('name')->get(['id', 'name', 'phone']);
        $users    = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calls.create', compact('call', 'contacts', 'leads', 'accounts', 'users'));
    }

    public function update(UpdateCallLogRequest $request, CallLog $callLog): RedirectResponse
    {
        $callLog->update($request->validated());

        return redirect()->route('activity.calls.show', $callLog)
            ->with('success', 'Call log updated successfully.');
    }

    public function destroy(CallLog $callLog): RedirectResponse
    {
        $callLog->delete();

        return redirect()->route('activity.calls.index')
            ->with('success', 'Call log deleted successfully.');
    }
}
