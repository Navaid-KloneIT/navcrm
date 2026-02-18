<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\WebForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LandingPageWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = LandingPage::with(['webForm', 'creator']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', (bool) $request->get('active'));
        }

        $pages = $query->latest()->paginate(25)->withQueryString();

        return view('marketing.landing-pages.index', compact('pages'));
    }

    public function create(): View
    {
        $forms       = WebForm::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $landingPage = null;

        return view('marketing.landing-pages.create', compact('landingPage', 'forms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
            'web_form_id'      => ['nullable', 'exists:web_forms,id'],
        ]);

        $validated['slug']       = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        $page = LandingPage::create($validated);

        return redirect()->route('marketing.landing-pages.show', $page)
            ->with('success', 'Landing page created successfully.');
    }

    public function show(LandingPage $landingPage): View
    {
        $landingPage->load(['webForm', 'creator']);

        return view('marketing.landing-pages.show', compact('landingPage'));
    }

    public function edit(LandingPage $landingPage): View
    {
        $forms = WebForm::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('marketing.landing-pages.create', compact('landingPage', 'forms'));
    }

    public function update(Request $request, LandingPage $landingPage): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active'        => ['nullable', 'boolean'],
            'web_form_id'      => ['nullable', 'exists:web_forms,id'],
        ]);

        $validated['slug']      = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $landingPage->update($validated);

        return redirect()->route('marketing.landing-pages.show', $landingPage)
            ->with('success', 'Landing page updated successfully.');
    }

    public function destroy(LandingPage $landingPage): RedirectResponse
    {
        $landingPage->delete();

        return redirect()->route('marketing.landing-pages.index')
            ->with('success', 'Landing page deleted successfully.');
    }
}
