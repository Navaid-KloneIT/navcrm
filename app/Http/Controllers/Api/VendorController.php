<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::withCount('purchaseOrders');

        $query->search($request->get('search'), ['company_name', 'contact_name', 'email']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $vendors = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($vendors);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name'    => ['required', 'string', 'max:255'],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:40'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'state'           => ['nullable', 'string', 'max:100'],
            'country'         => ['nullable', 'string', 'max:100'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'website'         => ['nullable', 'url', 'max:255'],
            'notes'           => ['nullable', 'string'],
            'status'          => ['required', 'string', 'in:active,inactive'],
            'portal_password' => ['nullable', 'string', 'min:6'],
            'portal_active'   => ['nullable', 'boolean'],
        ]);

        $validated['vendor_number'] = $this->generateVendorNumber();

        if (! empty($validated['portal_password'])) {
            $validated['portal_password'] = Hash::make($validated['portal_password']);
        } else {
            unset($validated['portal_password']);
        }

        $vendor = Vendor::create($validated);

        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor->load('purchaseOrders'));
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'company_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'contact_name'    => ['nullable', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:40'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'state'           => ['nullable', 'string', 'max:100'],
            'country'         => ['nullable', 'string', 'max:100'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'website'         => ['nullable', 'url', 'max:255'],
            'notes'           => ['nullable', 'string'],
            'status'          => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'portal_password' => ['nullable', 'string', 'min:6'],
            'portal_active'   => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['portal_password'])) {
            $validated['portal_password'] = Hash::make($validated['portal_password']);
        } else {
            unset($validated['portal_password']);
        }

        $vendor->update($validated);

        return response()->json($vendor->fresh());
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();

        return response()->json(null, 204);
    }

    private function generateVendorNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = Vendor::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $next = $last ? ((int) ltrim(substr($last->vendor_number, 3), '0') + 1) : 1;

        return 'VN-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
