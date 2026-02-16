<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\ContactResource;
use App\Models\Account;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($industry = $request->input('industry')) {
            $query->where('industry', $industry);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $accounts = $query->with('owner')->paginate($request->input('per_page', 15));

        return response()->json(AccountResource::collection($accounts)->response()->getData(true));
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $account = Account::create($validated);

        return response()->json([
            'account' => new AccountResource($account->load('owner')),
        ], 201);
    }

    public function show(Account $account): JsonResponse
    {
        $account->load([
            'parent',
            'children',
            'owner',
            'contacts',
            'addresses',
            'activities' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return response()->json([
            'account' => new AccountResource($account),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json([
            'account' => new AccountResource($account->fresh()->load('owner')),
        ]);
    }

    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json(null, 204);
    }

    public function contacts(Account $account): JsonResponse
    {
        $contacts = $account->contacts()->paginate(15);

        return response()->json(ContactResource::collection($contacts)->response()->getData(true));
    }

    public function attachContact(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'role' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $account->contacts()->attach($validated['contact_id'], [
            'role' => $validated['role'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return response()->json([
            'message' => 'Contact attached to account successfully.',
        ], 201);
    }

    public function detachContact(Account $account, Contact $contact): JsonResponse
    {
        $account->contacts()->detach($contact->id);

        return response()->json(null, 204);
    }

    public function children(Account $account): JsonResponse
    {
        $children = $account->children()->get();

        return response()->json([
            'accounts' => AccountResource::collection($children),
        ]);
    }
}
