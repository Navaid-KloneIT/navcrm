<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarEvent\StoreCalendarEventRequest;
use App\Http\Requests\CalendarEvent\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CalendarEvent::with(['organizer', 'eventable']);

        if ($type = $request->get('event_type')) {
            $query->where('event_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('starts_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('starts_at', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $events = $query->orderBy('starts_at', 'desc')->paginate(25);

        return response()->json($events);
    }

    public function store(StoreCalendarEventRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $event = CalendarEvent::create([
            ...$validated,
            'tenant_id'    => auth()->user()->tenant_id,
            'organizer_id' => $validated['organizer_id'] ?? auth()->id(),
        ]);

        return response()->json($event->load(['organizer']), 201);
    }

    public function show(CalendarEvent $calendarEvent): JsonResponse
    {
        return response()->json($calendarEvent->load(['organizer', 'eventable']));
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->update($request->validated());

        return response()->json($calendarEvent->fresh(['organizer']));
    }

    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->delete();

        return response()->json(null, 204);
    }
}
