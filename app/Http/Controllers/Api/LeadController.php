<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\ConvertLeadRequest;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\LeadConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        protected LeadConversionService $leadConversionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Lead::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($score = $request->input('score')) {
            $query->where('score', $score);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($request->has('is_converted')) {
            $query->where('is_converted', $request->boolean('is_converted'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $leads = $query->with(['owner', 'tags'])->paginate($request->input('per_page', 15));

        return response()->json(LeadResource::collection($leads)->response()->getData(true));
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $lead = Lead::create($validated);

        return response()->json([
            'lead' => new LeadResource($lead->load('owner', 'tags')),
        ], 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $lead->load([
            'owner',
            'tags',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            },
            'notes',
            'convertedContact',
            'convertedAccount',
        ]);

        return response()->json([
            'lead' => new LeadResource($lead),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $lead->update($request->validated());

        return response()->json([
            'lead' => new LeadResource($lead->fresh()->load('owner', 'tags')),
        ]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(null, 204);
    }

    public function convert(ConvertLeadRequest $request, Lead $lead): JsonResponse
    {
        if ($lead->is_converted) {
            return response()->json([
                'message' => 'This lead has already been converted.',
            ], 422);
        }

        $result = $this->leadConversionService->convert($lead, $request->validated());

        return response()->json([
            'contact' => new ContactResource($result['contact']),
            'account' => $result['account'] ? new AccountResource($result['account']) : null,
            'lead' => new LeadResource($result['lead']),
        ]);
    }

    public function syncTags(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $lead->tags()->sync($validated['tag_ids']);

        return response()->json([
            'lead' => new LeadResource($lead->fresh()->load('tags')),
        ]);
    }
}
