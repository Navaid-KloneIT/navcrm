<?php

namespace App\Http\Controllers;

use App\Enums\VendorStatus;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VendorWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vendor::withCount('purchaseOrders');

        $query->search($request->get('search'), ['company_name', 'contact_name', 'email']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $vendors = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'    => Vendor::count(),
            'active'   => Vendor::where('status', 'active')->count(),
            'inactive' => Vendor::where('status', 'inactive')->count(),
        ];

        return view('inventory.vendors.index', compact('vendors', 'stats'));
    }

    public function create(): View
    {
        $vendor = null;

        return view('inventory.vendors.create', compact('vendor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateVendor($request);

        $tenantId = auth()->user()->tenant_id;
        $last = Vendor::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();
        $next = $last ? ((int) ltrim(substr($last->vendor_number, 3), '0') + 1) : 1;
        $validated['vendor_number'] = 'VN-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        if (! empty($validated['portal_password'])) {
            $validated['portal_password'] = Hash::make($validated['portal_password']);
        } else {
            unset($validated['portal_password']);
        }

        $vendor = Vendor::create($validated);

        return redirect()->route('inventory.vendors.show', $vendor)
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load(['purchaseOrders' => fn ($q) => $q->latest()->limit(20)]);

        return view('inventory.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor): View
    {
        return view('inventory.vendors.create', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $validated = $this->validateVendor($request);

        if (! empty($validated['portal_password'])) {
            $validated['portal_password'] = Hash::make($validated['portal_password']);
        } else {
            unset($validated['portal_password']);
        }

        $vendor->update($validated);

        return redirect()->route('inventory.vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return redirect()->route('inventory.vendors.index')
            ->with('success', 'Vendor deleted.');
    }

    private function validateVendor(Request $request): array
    {
        return $request->validate([
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
    }
}
