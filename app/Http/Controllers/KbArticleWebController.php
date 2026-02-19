<?php

namespace App\Http\Controllers;

use App\Http\Requests\KbArticle\StoreKbArticleRequest;
use App\Http\Requests\KbArticle\UpdateKbArticleRequest;
use App\Models\KbArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KbArticleWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = KbArticle::with(['author']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->get('visibility') === 'public') {
            $query->where('is_public', true);
        } elseif ($request->get('visibility') === 'internal') {
            $query->where('is_public', false);
        }

        if ($request->get('published') === '1') {
            $query->where('is_published', true);
        } elseif ($request->get('published') === '0') {
            $query->where('is_published', false);
        }

        $articles   = $query->latest()->paginate(25)->withQueryString();
        $categories = KbArticle::distinct()->orderBy('category')->pluck('category')->filter()->values();

        return view('support.kb-articles.index', compact('articles', 'categories'));
    }

    public function create(): View
    {
        $article    = null;
        $categories = KbArticle::distinct()->orderBy('category')->pluck('category')->filter()->values();

        return view('support.kb-articles.create', compact('article', 'categories'));
    }

    public function store(StoreKbArticleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['author_id']    = auth()->id();
        $validated['is_public']    = $request->boolean('is_public');
        $validated['is_published'] = $request->boolean('is_published');

        $article = KbArticle::create($validated);

        return redirect()->route('support.kb-articles.show', $article)
            ->with('success', 'Article created successfully.');
    }

    public function show(KbArticle $kbArticle): View
    {
        $kbArticle->load('author');
        $kbArticle->increment('view_count');

        return view('support.kb-articles.show', compact('kbArticle'));
    }

    public function edit(KbArticle $kbArticle): View
    {
        $article    = $kbArticle;
        $categories = KbArticle::distinct()->orderBy('category')->pluck('category')->filter()->values();

        return view('support.kb-articles.create', compact('article', 'categories'));
    }

    public function update(UpdateKbArticleRequest $request, KbArticle $kbArticle): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_public']    = $request->boolean('is_public');
        $validated['is_published'] = $request->boolean('is_published');

        $kbArticle->update($validated);

        return redirect()->route('support.kb-articles.show', $kbArticle)
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(KbArticle $kbArticle): RedirectResponse
    {
        $kbArticle->delete();

        return redirect()->route('support.kb-articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
