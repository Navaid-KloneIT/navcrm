<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::all();

        return response()->json([
            'tags' => TagResource::collection($tags),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $tag = Tag::create($validated);

        return response()->json([
            'tag' => new TagResource($tag),
        ], 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $tag->update($validated);

        return response()->json([
            'tag' => new TagResource($tag->fresh()),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}
