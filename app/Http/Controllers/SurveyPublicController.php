<?php

namespace App\Http\Controllers;

use App\Enums\SurveyStatus;
use App\Models\Contact;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyPublicController extends Controller
{
    /**
     * Show the public survey response form.
     */
    public function show(string $token): View
    {
        $survey = Survey::where('token', $token)->firstOrFail();

        abort_if($survey->status !== SurveyStatus::Active, 404, 'This survey is no longer accepting responses.');

        return view('success.surveys.respond', compact('survey'));
    }

    /**
     * Submit a response to a public survey.
     */
    public function respond(Request $request, string $token)
    {
        $survey = Survey::where('token', $token)->firstOrFail();

        abort_if($survey->status !== SurveyStatus::Active, 404, 'This survey is no longer accepting responses.');

        $validated = $request->validate([
            'score'   => ['required', 'integer', 'min:0', 'max:10'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'email'   => ['nullable', 'email', 'max:255'],
        ]);

        // Try to find the contact by email
        $contactId = null;
        $accountId = $survey->account_id;
        if (! empty($validated['email'])) {
            $contact = Contact::where('email', $validated['email'])
                ->where('tenant_id', $survey->tenant_id)
                ->first();
            if ($contact) {
                $contactId = $contact->id;
                // Use the contact's first account if survey doesn't have one
                if (! $accountId) {
                    $firstAccount = $contact->accounts()->first();
                    $accountId = $firstAccount?->id;
                }
            }
        }

        SurveyResponse::create([
            'survey_id'    => $survey->id,
            'tenant_id'    => $survey->tenant_id,
            'contact_id'   => $contactId,
            'account_id'   => $accountId,
            'score'        => $validated['score'],
            'comment'      => $validated['comment'] ?? null,
            'responded_at' => now(),
        ]);

        return view('success.surveys.respond', [
            'survey'    => $survey,
            'submitted' => true,
        ]);
    }
}
