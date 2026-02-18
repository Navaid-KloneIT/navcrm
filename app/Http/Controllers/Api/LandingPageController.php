<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LandingPage::with(['webForm', 'creator']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $pages = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
            'web_form_id'      => ['nullable', 'exists:web_forms,id'],
        ]);

        $validated['slug']       = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active']  = $validated['is_active'] ?? true;
        $validated['created_by'] = auth()->id();

        $page = LandingPage::create($validated);

        return response()->json($page->load('webForm'), 201);
    }

    public function show(LandingPage $landingPage): JsonResponse
    {
        return response()->json($landingPage->load(['webForm', 'creator']));
    }

    public function update(Request $request, LandingPage $landingPage): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
            'web_form_id'      => ['nullable', 'exists:web_forms,id'],
        ]);

        $landingPage->update($validated);

        return response()->json($landingPage->fresh('webForm'));
    }

    public function destroy(LandingPage $landingPage): JsonResponse
    {
        $landingPage->delete();

        return response()->json(null, 204);
    }
}
