<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tagId = $request->input('tag_id')) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $contacts = $query->with(['owner', 'tags'])->paginate($request->input('per_page', 15));

        return response()->json(ContactResource::collection($contacts)->response()->getData(true));
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $contact = Contact::create($validated);

        return response()->json([
            'contact' => new ContactResource($contact->load('owner', 'tags')),
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load([
            'accounts',
            'tags',
            'owner',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            },
            'notes',
        ]);

        return response()->json([
            'contact' => new ContactResource($contact),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->update($request->validated());

        return response()->json([
            'contact' => new ContactResource($contact->fresh()->load('owner', 'tags')),
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }

    public function syncTags(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $contact->tags()->sync($validated['tag_ids']);

        return response()->json([
            'contact' => new ContactResource($contact->fresh()->load('tags')),
        ]);
    }

    public function relationships(Contact $contact): JsonResponse
    {
        $relatedContacts = $contact->relatedContacts()->get();
        $relatedFrom = $contact->relatedFrom()->get();

        $allRelated = $relatedContacts->merge($relatedFrom)->unique('id');

        $relationships = $allRelated->map(function ($related) {
            return [
                'id' => $related->id,
                'first_name' => $related->first_name,
                'last_name' => $related->last_name,
                'full_name' => $related->full_name,
                'email' => $related->email,
                'relationship_type' => $related->pivot->relationship_type,
            ];
        });

        return response()->json([
            'relationships' => $relationships->values(),
        ]);
    }

    public function addRelationship(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'related_contact_id' => ['required', 'exists:contacts,id', 'different:contact_id'],
            'relationship_type' => ['required', 'string', 'max:255'],
        ]);

        if ($validated['related_contact_id'] == $contact->id) {
            return response()->json([
                'message' => 'A contact cannot be related to itself.',
            ], 422);
        }

        $contact->relatedContacts()->attach($validated['related_contact_id'], [
            'relationship_type' => $validated['relationship_type'],
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return response()->json([
            'message' => 'Relationship added successfully.',
        ], 201);
    }

    public function removeRelationship(Contact $contact, Contact $related): JsonResponse
    {
        $contact->relatedContacts()->detach($related->id);
        $contact->relatedFrom()->detach($related->id);

        return response()->json(null, 204);
    }
}
