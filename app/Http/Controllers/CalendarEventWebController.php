<?php

namespace App\Http\Controllers;

use App\Enums\CalendarEventStatus;
use App\Http\Requests\CalendarEvent\StoreCalendarEventRequest;
use App\Http\Requests\CalendarEvent\UpdateCalendarEventRequest;
use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarEventWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = CalendarEvent::with(['organizer', 'eventable']);

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($type = $request->get('event_type')) {
            $query->where('event_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($organizer = $request->get('organizer_id')) {
            $query->where('organizer_id', $organizer);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('starts_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('starts_at', '<=', $to);
        }

        $events = $query->orderBy('starts_at', 'desc')->paginate(25)->withQueryString();
        $users  = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calendar.index', compact('events', 'users'));
    }

    public function create(): View
    {
        $event        = null;
        $contacts     = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $accounts     = Account::orderBy('name')->get(['id', 'name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users        = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calendar.create', compact('event', 'contacts', 'accounts', 'opportunities', 'users'));
    }

    public function store(StoreCalendarEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $event = CalendarEvent::create([
            ...$validated,
            'tenant_id'    => auth()->user()->tenant_id,
            'organizer_id' => $validated['organizer_id'] ?? auth()->id(),
        ]);

        return redirect()->route('activity.calendar.show', $event)
            ->with('success', "Event \"{$event->title}\" created successfully.");
    }

    public function show(CalendarEvent $calendarEvent): View
    {
        $calendarEvent->load(['organizer', 'eventable']);

        return view('activity.calendar.show', ['event' => $calendarEvent]);
    }

    public function edit(CalendarEvent $calendarEvent): View
    {
        $event        = $calendarEvent;
        $contacts     = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $accounts     = Account::orderBy('name')->get(['id', 'name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users        = User::orderBy('name')->get(['id', 'name']);

        return view('activity.calendar.create', compact('event', 'contacts', 'accounts', 'opportunities', 'users'));
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $calendarEvent): RedirectResponse
    {
        $calendarEvent->update($request->validated());

        return redirect()->route('activity.calendar.show', $calendarEvent)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(CalendarEvent $calendarEvent): RedirectResponse
    {
        $calendarEvent->delete();

        return redirect()->route('activity.calendar.index')
            ->with('success', 'Event deleted successfully.');
    }
}
