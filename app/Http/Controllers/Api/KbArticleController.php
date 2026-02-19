<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KbArticle::with('author');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->has('is_public')) {
            $query->where('is_public', (bool) $request->get('is_public'));
        }

        if ($request->has('is_published')) {
            $query->where('is_published', (bool) $request->get('is_published'));
        }

        $articles = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($articles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:100'],
            'body'         => ['required', 'string'],
            'is_public'    => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        $validated['author_id'] = auth()->id();

        $article = KbArticle::create($validated);

        return response()->json($article->load('author'), 201);
    }

    public function show(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->increment('view_count');

        return response()->json($kbArticle->load('author'));
    }

    public function update(Request $request, KbArticle $kbArticle): JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:100'],
            'body'         => ['sometimes', 'required', 'string'],
            'is_public'    => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        $kbArticle->update($validated);

        return response()->json($kbArticle->fresh('author'));
    }

    public function destroy(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->delete();

        return response()->json(null, 204);
    }
}
