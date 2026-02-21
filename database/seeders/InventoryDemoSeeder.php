<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $users    = User::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        $admin = $users->first();

        // ── Set stock levels on existing products ──────────────────────────
        foreach ($products as $i => $product) {
            $stockQty     = [150, 75, 200, 30, 10, 500, 80, 45, 120, 60][$i % 10];
            $reorderLevel = [20, 15, 30, 10, 5, 50, 20, 10, 25, 15][$i % 10];

            $product->update([
                'stock_quantity' => $stockQty,
                'reorder_level'  => $reorderLevel,
            ]);

            // Initial stock movement
            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $product->id,
                'type'       => 'adjustment',
                'quantity'   => $stockQty,
                'notes'      => 'Initial stock count',
                'created_by' => $admin->id,
            ]);
        }

        // ── Vendors ────────────────────────────────────────────────────────
        $vendorsData = [
            ['company_name' => 'Acme Supplies Co.', 'contact_name' => 'John Smith', 'email' => 'john@acmesupplies.com', 'phone' => '+1-555-0101', 'city' => 'New York', 'state' => 'NY', 'country' => 'United States', 'status' => 'active', 'portal_active' => true],
            ['company_name' => 'Global Parts Ltd.', 'contact_name' => 'Maria Garcia', 'email' => 'maria@globalparts.com', 'phone' => '+1-555-0202', 'city' => 'Los Angeles', 'state' => 'CA', 'country' => 'United States', 'status' => 'active', 'portal_active' => true],
            ['company_name' => 'TechWare Solutions', 'contact_name' => 'David Chen', 'email' => 'david@techware.com', 'phone' => '+1-555-0303', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'United States', 'status' => 'active', 'portal_active' => false],
            ['company_name' => 'Nordic Components AB', 'contact_name' => 'Erik Lindqvist', 'email' => 'erik@nordiccomp.se', 'phone' => '+46-70-1234567', 'city' => 'Stockholm', 'country' => 'Sweden', 'status' => 'active', 'portal_active' => false],
            ['company_name' => 'Sunrise Wholesale', 'contact_name' => 'Priya Sharma', 'email' => 'priya@sunrisewholesale.in', 'phone' => '+91-9876543210', 'city' => 'Mumbai', 'country' => 'India', 'status' => 'inactive', 'portal_active' => false],
        ];

        $vendors = [];
        foreach ($vendorsData as $i => $data) {
            $data['tenant_id']     = $tenant->id;
            $data['vendor_number'] = 'VN-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            if ($data['portal_active']) {
                $data['portal_password'] = Hash::make('vendor123');
            }
            $vendors[] = Vendor::create($data);
        }

        // ── Purchase Orders ────────────────────────────────────────────────
        $productList = $products->take(6);

        // PO 1: Received (completed)
        $po1 = PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00001',
            'vendor_id'     => $vendors[0]->id,
            'status'        => 'received',
            'order_date'    => now()->subDays(30),
            'expected_date' => now()->subDays(15),
            'received_date' => now()->subDays(12),
            'subtotal'      => 5000.00,
            'tax_amount'    => 500.00,
            'total_amount'  => 5500.00,
            'notes'         => 'Regular quarterly restock order.',
            'created_by'    => $admin->id,
            'approved_by'   => $admin->id,
            'approved_at'   => now()->subDays(28),
        ]);

        if ($productList->count() >= 2) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po1->id,
                'product_id'        => $productList[0]->id,
                'description'       => $productList[0]->name,
                'quantity'          => 50,
                'unit_price'        => 60.00,
                'tax_rate'          => 10,
                'total'             => 3300.00,
                'received_quantity' => 50,
            ]);
            PurchaseOrderItem::create([
                'purchase_order_id' => $po1->id,
                'product_id'        => $productList[1]->id,
                'description'       => $productList[1]->name,
                'quantity'          => 40,
                'unit_price'        => 50.00,
                'tax_rate'          => 10,
                'total'             => 2200.00,
                'received_quantity' => 40,
            ]);

            // Stock movements for received PO
            StockMovement::create([
                'tenant_id'      => $tenant->id,
                'product_id'     => $productList[0]->id,
                'type'           => 'purchase_in',
                'quantity'       => 50,
                'reference_type' => PurchaseOrder::class,
                'reference_id'   => $po1->id,
                'notes'          => "Stock received via PO PO-00001",
                'created_by'     => null,
            ]);
            StockMovement::create([
                'tenant_id'      => $tenant->id,
                'product_id'     => $productList[1]->id,
                'type'           => 'purchase_in',
                'quantity'       => 40,
                'reference_type' => PurchaseOrder::class,
                'reference_id'   => $po1->id,
                'notes'          => "Stock received via PO PO-00001",
                'created_by'     => null,
            ]);
        }

        // PO 2: Approved (waiting to receive)
        $po2 = PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00002',
            'vendor_id'     => $vendors[1]->id,
            'status'        => 'approved',
            'order_date'    => now()->subDays(10),
            'expected_date' => now()->addDays(5),
            'subtotal'      => 3200.00,
            'tax_amount'    => 320.00,
            'total_amount'  => 3520.00,
            'notes'         => 'Urgent restock for low-stock items.',
            'created_by'    => $admin->id,
            'approved_by'   => $admin->id,
            'approved_at'   => now()->subDays(8),
        ]);

        if ($productList->count() >= 4) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po2->id,
                'product_id'        => $productList[2]->id,
                'description'       => $productList[2]->name,
                'quantity'          => 80,
                'unit_price'        => 40.00,
                'tax_rate'          => 10,
                'total'             => 3520.00,
            ]);
        }

        // PO 3: Submitted (pending approval)
        $po3 = PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00003',
            'vendor_id'     => $vendors[2]->id,
            'status'        => 'submitted',
            'order_date'    => now()->subDays(3),
            'expected_date' => now()->addDays(14),
            'subtotal'      => 1500.00,
            'tax_amount'    => 150.00,
            'total_amount'  => 1650.00,
            'created_by'    => $admin->id,
        ]);

        if ($productList->count() >= 5) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po3->id,
                'product_id'        => $productList[4]->id,
                'description'       => $productList[4]->name,
                'quantity'          => 30,
                'unit_price'        => 50.00,
                'tax_rate'          => 10,
                'total'             => 1650.00,
            ]);
        }

        // PO 4: Draft
        $po4 = PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00004',
            'vendor_id'     => $vendors[0]->id,
            'status'        => 'draft',
            'order_date'    => now(),
            'expected_date' => now()->addDays(21),
            'subtotal'      => 800.00,
            'tax_amount'    => 80.00,
            'total_amount'  => 880.00,
            'created_by'    => $admin->id,
        ]);

        if ($productList->count() >= 3) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po4->id,
                'product_id'        => $productList[2]->id,
                'description'       => $productList[2]->name,
                'quantity'          => 20,
                'unit_price'        => 40.00,
                'tax_rate'          => 10,
                'total'             => 880.00,
            ]);
        }

        // PO 5: Cancelled
        PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00005',
            'vendor_id'     => $vendors[3]->id,
            'status'        => 'cancelled',
            'order_date'    => now()->subDays(45),
            'subtotal'      => 2000.00,
            'tax_amount'    => 200.00,
            'total_amount'  => 2200.00,
            'notes'         => 'Cancelled — vendor unable to fulfil.',
            'created_by'    => $admin->id,
        ]);

        // PO 6: Another received
        $po6 = PurchaseOrder::create([
            'tenant_id'     => $tenant->id,
            'po_number'     => 'PO-00006',
            'vendor_id'     => $vendors[1]->id,
            'status'        => 'received',
            'order_date'    => now()->subDays(60),
            'expected_date' => now()->subDays(45),
            'received_date' => now()->subDays(42),
            'subtotal'      => 4500.00,
            'tax_amount'    => 450.00,
            'total_amount'  => 4950.00,
            'created_by'    => $admin->id,
            'approved_by'   => $admin->id,
            'approved_at'   => now()->subDays(58),
        ]);

        if ($productList->count() >= 6) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po6->id,
                'product_id'        => $productList[5]->id,
                'description'       => $productList[5]->name,
                'quantity'          => 100,
                'unit_price'        => 45.00,
                'tax_rate'          => 10,
                'total'             => 4950.00,
                'received_quantity' => 100,
            ]);
        }

        // ── Additional stock movements (sale_out examples) ─────────────────
        if ($productList->count() >= 2) {
            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $productList[0]->id,
                'type'       => 'sale_out',
                'quantity'   => -15,
                'notes'      => 'Auto-deduct for Invoice INV-00001',
                'created_by' => null,
            ]);

            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $productList[1]->id,
                'type'       => 'sale_out',
                'quantity'   => -8,
                'notes'      => 'Auto-deduct for Invoice INV-00002',
                'created_by' => null,
            ]);

            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $productList[0]->id,
                'type'       => 'adjustment',
                'quantity'   => -3,
                'notes'      => 'Damaged goods write-off',
                'created_by' => $admin->id,
            ]);

            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $productList[1]->id,
                'type'       => 'return_in',
                'quantity'   => 2,
                'notes'      => 'Customer return — order #1042',
                'created_by' => $admin->id,
            ]);
        }
    }
}
