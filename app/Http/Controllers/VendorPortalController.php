<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VendorPortalController extends Controller
{
    public function showLogin(): View
    {
        return view('vendor-portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $vendor = Vendor::withoutGlobalScopes()
            ->where('email', $request->email)
            ->where('portal_active', true)
            ->first();

        if (! $vendor || ! Hash::check($request->password, $vendor->portal_password)) {
            return back()->withErrors(['email' => 'Invalid credentials or portal access not enabled.'])->onlyInput('email');
        }

        session([
            'vendor_portal_id'        => $vendor->id,
            'vendor_portal_tenant_id' => $vendor->tenant_id,
        ]);

        return redirect()->route('vendor-portal.dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->forget(['vendor_portal_id', 'vendor_portal_tenant_id']);

        return redirect()->route('vendor-portal.login')->with('success', 'You have been logged out.');
    }

    public function dashboard(): View
    {
        $vendor = Vendor::withoutGlobalScopes()->findOrFail(session('vendor_portal_id'));

        $recentPOs = PurchaseOrder::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->latest('order_date')
            ->take(5)
            ->get();

        $stats = [
            'open_pos'     => PurchaseOrder::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', ['draft', 'submitted', 'approved'])
                ->count(),
            'total_value'  => PurchaseOrder::withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', ['approved', 'received'])
                ->sum('total_amount'),
        ];

        return view('vendor-portal.dashboard', compact('vendor', 'recentPOs', 'stats'));
    }

    public function purchaseOrders(): View
    {
        $vendor = Vendor::withoutGlobalScopes()->findOrFail(session('vendor_portal_id'));

        $purchaseOrders = PurchaseOrder::withoutGlobalScopes()
            ->where('vendor_id', $vendor->id)
            ->with('items.product')
            ->latest('order_date')
            ->paginate(25);

        return view('vendor-portal.purchase-orders', compact('vendor', 'purchaseOrders'));
    }

    public function stockCheck(): View
    {
        $tenantId = session('vendor_portal_tenant_id');

        $products = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('vendor-portal.stock-check', compact('products'));
    }

    public function registerLead(): View
    {
        return view('vendor-portal.register-lead');
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
        ]);

        $tenantId = session('vendor_portal_tenant_id');
        $vendorId = session('vendor_portal_id');

        Lead::withoutGlobalScopes()->create([
            'tenant_id'    => $tenantId,
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'description'  => $validated['description'] ?? null,
            'source'       => 'vendor_portal',
            'status'       => 'new',
        ]);

        return redirect()->route('vendor-portal.register-lead')
            ->with('success', 'Lead registered successfully. Thank you!');
    }
}
