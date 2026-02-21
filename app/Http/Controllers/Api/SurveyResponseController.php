<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyResponseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SurveyResponse::with(['survey', 'contact', 'account']);

        if ($surveyId = $request->get('survey_id')) {
            $query->where('survey_id', $surveyId);
        }
        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }
        if ($contactId = $request->get('contact_id')) {
            $query->where('contact_id', $contactId);
        }

        $responses = $query->orderByDesc('responded_at')
            ->paginate($request->get('per_page', 25));

        return response()->json($responses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'survey_id'  => ['required', 'integer', 'exists:surveys,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'score'      => ['required', 'integer', 'min:0', 'max:10'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['responded_at'] = now();

        $response = SurveyResponse::create($validated);

        return response()->json($response->load(['survey', 'contact', 'account']), 201);
    }
}
