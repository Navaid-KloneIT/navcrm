<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Note::query();

        if ($notableType = $request->input('notable_type')) {
            $query->where('notable_type', $notableType);
        }

        if ($notableId = $request->input('notable_id')) {
            $query->where('notable_id', $notableId);
        }

        $notes = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json(NoteResource::collection($notes)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
            'notable_type' => ['required', 'string', 'max:255'],
            'notable_id' => ['required', 'integer'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $note = Note::create($validated);

        return response()->json([
            'note' => new NoteResource($note->load('user')),
        ], 201);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $note->update($validated);

        return response()->json([
            'note' => new NoteResource($note->fresh()->load('user')),
        ]);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json(null, 204);
    }
}
