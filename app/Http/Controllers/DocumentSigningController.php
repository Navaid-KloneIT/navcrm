<?php

namespace App\Http\Controllers;

use App\Models\DocumentSignatory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentSigningController extends Controller
{
    public function show(string $token): View
    {
        $signatory = DocumentSignatory::where('sign_token', $token)
            ->with('document')
            ->firstOrFail();

        // Mark as viewed on first open
        if ($signatory->status === 'pending') {
            $signatory->update(['status' => 'viewed', 'viewed_at' => now()]);

            // Update document status to 'viewed' if still 'sent'
            if ($signatory->document->status->value === 'sent') {
                $signatory->document->update(['status' => 'viewed']);
            }
        }

        return view('signing.show', compact('signatory'));
    }

    public function sign(Request $request, string $token): RedirectResponse
    {
        $signatory = DocumentSignatory::where('sign_token', $token)
            ->with('document')
            ->firstOrFail();

        if (in_array($signatory->status, ['signed', 'rejected'])) {
            return redirect()->route('signing.show', $token)
                ->with('error', 'This document has already been ' . $signatory->status . '.');
        }

        $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $signatory->update([
            'status'         => 'signed',
            'signed_at'      => now(),
            'signature_data' => $request->signature_data,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        // Check if all signatories have signed
        $document       = $signatory->document;
        $pendingCount   = $document->signatories()
            ->whereNotIn('status', ['signed'])
            ->count();

        if ($pendingCount === 0) {
            $document->update(['status' => 'signed']);
        }

        return redirect()->route('signing.show', $token)
            ->with('success', 'Thank you! Your signature has been recorded.');
    }
}
