<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\WebForm;
use App\Models\WebFormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebFormController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WebForm::with(['creator', 'assignedUser']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $forms = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($forms);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'fields'               => ['nullable', 'array'],
            'submit_button_text'   => ['nullable', 'string', 'max:100'],
            'success_message'      => ['nullable', 'string'],
            'redirect_url'         => ['nullable', 'url'],
            'assign_to_user_id'    => ['nullable', 'exists:users,id'],
            'assign_by_geography'  => ['nullable', 'boolean'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $validated['created_by']          = auth()->id();
        $validated['is_active']           = $validated['is_active'] ?? true;
        $validated['assign_by_geography'] = $validated['assign_by_geography'] ?? false;

        $form = WebForm::create($validated);

        return response()->json($form, 201);
    }

    public function show(WebForm $webForm): JsonResponse
    {
        return response()->json($webForm->load(['creator', 'assignedUser']));
    }

    public function update(Request $request, WebForm $webForm): JsonResponse
    {
        $validated = $request->validate([
            'name'                 => ['sometimes', 'required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'fields'               => ['nullable', 'array'],
            'submit_button_text'   => ['nullable', 'string', 'max:100'],
            'success_message'      => ['nullable', 'string'],
            'redirect_url'         => ['nullable', 'url'],
            'assign_to_user_id'    => ['nullable', 'exists:users,id'],
            'assign_by_geography'  => ['nullable', 'boolean'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $webForm->update($validated);

        return response()->json($webForm->fresh(['creator', 'assignedUser']));
    }

    public function destroy(WebForm $webForm): JsonResponse
    {
        $webForm->delete();

        return response()->json(null, 204);
    }

    public function submissions(Request $request, WebForm $webForm): JsonResponse
    {
        $submissions = $webForm->submissions()
            ->when($request->boolean('unconverted'), fn($q) => $q->where('is_converted', false))
            ->latest()
            ->paginate($request->get('per_page', 25));

        return response()->json($submissions);
    }

    public function convertSubmission(Request $request, WebFormSubmission $submission): JsonResponse
    {
        if ($submission->is_converted) {
            return response()->json(['message' => 'Already converted.'], 422);
        }

        $data = $submission->data;

        $lead = Lead::create([
            'first_name'   => $data['first_name'] ?? 'Unknown',
            'last_name'    => $data['last_name']  ?? 'Lead',
            'email'        => $data['email']      ?? null,
            'phone'        => $data['phone']      ?? null,
            'company_name' => $data['company']    ?? null,
            'source'       => 'Web Form',
            'owner_id'     => $submission->form->assign_to_user_id ?? auth()->id(),
            'created_by'   => auth()->id(),
        ]);

        $submission->update(['is_converted' => true, 'lead_id' => $lead->id]);

        return response()->json(['lead' => $lead, 'submission' => $submission], 201);
    }
}
