<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\KbArticle;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $contact = Contact::withoutGlobalScopes()
            ->where('email', $request->email)
            ->where('portal_active', true)
            ->first();

        if (! $contact || ! Hash::check($request->password, $contact->portal_password)) {
            return back()->withErrors(['email' => 'Invalid credentials or portal access not enabled.'])->onlyInput('email');
        }

        session([
            'portal_contact_id' => $contact->id,
            'portal_tenant_id'  => $contact->tenant_id,
        ]);

        return redirect()->route('portal.dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->forget(['portal_contact_id', 'portal_tenant_id']);

        return redirect()->route('portal.login')->with('success', 'You have been logged out.');
    }

    public function dashboard(): View
    {
        $contact = Contact::withoutGlobalScopes()->findOrFail(session('portal_contact_id'));

        $tickets = Ticket::withoutGlobalScopes()
            ->where('contact_id', $contact->id)
            ->latest()
            ->take(5)
            ->get();

        $articles = KbArticle::withoutGlobalScopes()
            ->where('tenant_id', $contact->tenant_id)
            ->where('is_public', true)
            ->where('is_published', true)
            ->latest()
            ->take(6)
            ->get();

        return view('portal.dashboard', compact('contact', 'tickets', 'articles'));
    }
}
