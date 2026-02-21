<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Survey::with(['account', 'ticket', 'creator']);

        $query->search($request->get('search'), ['name', 'survey_number']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $surveys = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($surveys);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', 'string', 'in:nps,csat'],
            'status'      => ['required', 'string', 'in:draft,active,closed'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'ticket_id'   => ['nullable', 'integer', 'exists:tickets,id'],
        ]);

        $validated['created_by']    = auth()->id();
        $validated['survey_number'] = $this->generateSurveyNumber();
        $validated['token']         = Str::random(64);

        $survey = Survey::create($validated);

        return response()->json($survey->load(['account', 'ticket', 'creator']), 201);
    }

    public function show(Survey $survey): JsonResponse
    {
        return response()->json(
            $survey->load(['account', 'ticket', 'creator', 'responses.contact', 'responses.account'])
        );
    }

    public function update(Request $request, Survey $survey): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['sometimes', 'required', 'string', 'in:nps,csat'],
            'status'      => ['sometimes', 'required', 'string', 'in:draft,active,closed'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'ticket_id'   => ['nullable', 'integer', 'exists:tickets,id'],
        ]);

        $survey->update($validated);

        return response()->json($survey->fresh(['account', 'ticket', 'creator']));
    }

    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();

        return response()->json(null, 204);
    }

    public function responses(Survey $survey, Request $request): JsonResponse
    {
        $responses = $survey->responses()
            ->with(['contact', 'account'])
            ->orderByDesc('responded_at')
            ->paginate($request->get('per_page', 25));

        return response()->json($responses);
    }

    private function generateSurveyNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = Survey::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('survey_number');

        $number = 1;
        if ($last && preg_match('/SV-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'SV-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
