<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Account;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Account $account): JsonResponse
    {
        $addresses = $account->addresses()->get();

        return response()->json([
            'addresses' => AddressResource::collection($addresses),
        ]);
    }

    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $address = $account->addresses()->create(array_merge($validated, [
            'tenant_id' => $request->user()->tenant_id,
        ]));

        return response()->json([
            'address' => new AddressResource($address),
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_1' => ['sometimes', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'country' => ['sometimes', 'string', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $address->update($validated);

        return response()->json([
            'address' => new AddressResource($address->fresh()),
        ]);
    }

    public function destroy(Address $address): JsonResponse
    {
        $address->delete();

        return response()->json(null, 204);
    }
}
