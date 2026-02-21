<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VendorPortalController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $vendor = Vendor::withoutGlobalScopes()
            ->where('email', $validated['email'])
            ->where('portal_active', true)
            ->first();

        if (! $vendor || ! Hash::check($validated['password'], $vendor->portal_password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json([
            'vendor'    => $vendor,
            'tenant_id' => $vendor->tenant_id,
        ]);
    }

    public function purchaseOrders(Request $request): JsonResponse
    {
        $vendorId = $request->get('vendor_id');

        $orders = PurchaseOrder::withoutGlobalScopes()
            ->where('vendor_id', $vendorId)
            ->with('items.product')
            ->latest('order_date')
            ->paginate($request->get('per_page', 25));

        return response()->json($orders);
    }

    public function stockCheck(Request $request): JsonResponse
    {
        $tenantId = $request->get('tenant_id');

        $products = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock_quantity', 'reorder_level', 'unit']);

        return response()->json($products);
    }

    public function registerLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'    => ['required', 'integer', 'exists:tenants,id'],
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'vendor_id'    => ['required', 'integer', 'exists:vendors,id'],
        ]);

        $lead = Lead::withoutGlobalScopes()->create([
            'tenant_id'    => $validated['tenant_id'],
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'description'  => $validated['description'] ?? null,
            'source'       => 'vendor_portal',
            'status'       => 'new',
        ]);

        return response()->json($lead, 201);
    }
}
