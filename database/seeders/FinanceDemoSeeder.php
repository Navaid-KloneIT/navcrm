<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        // BelongsToTenant scope is inactive in seeder context — direct where() queries required
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();

            if ($users->isEmpty()) {
                $this->command->warn("No users found for tenant [{$tenant->name}], skipping.");
                continue;
            }

            $accounts      = Account::where('tenant_id', $tenant->id)->get();
            $opportunities = Opportunity::where('tenant_id', $tenant->id)->get();
            $products      = Product::where('tenant_id', $tenant->id)->get();

            $this->command->info("Seeding Finance data for tenant: {$tenant->name}");

            $taxRates = $this->createTaxRates($tenant);
            $invoices = $this->createInvoices($tenant, $users, $accounts, $opportunities, $products, $taxRates);
            $this->createExpenses($tenant, $users, $accounts, $opportunities);
        }
    }

    private function createTaxRates(Tenant $tenant): array
    {
        $taxRates = [];

        $taxRates[] = TaxRate::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'GST',
            'rate'       => 10.00,
            'country'    => 'AU',
            'is_default' => true,
            'is_active'  => true,
        ]);

        $taxRates[] = TaxRate::create([
            'tenant_id'  => $tenant->id,
            'name'       => 'VAT',
            'rate'       => 20.00,
            'country'    => 'GB',
            'is_default' => false,
            'is_active'  => true,
        ]);

        return $taxRates;
    }

    private function createInvoices(
        Tenant $tenant,
        $users,
        $accounts,
        $opportunities,
        $products,
        array $taxRates
    ): array {
        $owner    = $users->first();
        $account  = $accounts->first();
        $opp      = $opportunities->first();

        if (! $account || ! $owner) {
            return [];
        }

        $createdInvoices = [];

        // Helper to get next invoice number
        $nextNumber = function () use ($tenant): string {
            $last = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->first();

            $seq = $last ? ((int) substr($last->invoice_number, 4)) + 1 : 1;
            return 'INV-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
        };

        $productList = $products->take(3)->values();

        // 1. Draft invoice
        $inv1 = Invoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => $nextNumber(),
            'account_id'     => $account->id,
            'opportunity_id' => $opp?->id,
            'owner_id'       => $owner->id,
            'created_by'     => $owner->id,
            'status'         => InvoiceStatus::Draft->value,
            'issue_date'     => now()->subDays(5),
            'due_date'       => now()->addDays(25),
            'currency'       => 'USD',
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_rate'       => 10.00,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
        ]);
        $this->addLineItems($inv1, $productList);
        $createdInvoices[] = $inv1;

        // 2. Sent invoice
        $inv2 = Invoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => $nextNumber(),
            'account_id'     => $accounts->get(1, $account)->id,
            'owner_id'       => $owner->id,
            'created_by'     => $owner->id,
            'status'         => InvoiceStatus::Sent->value,
            'issue_date'     => now()->subDays(10),
            'due_date'       => now()->addDays(20),
            'currency'       => 'USD',
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_rate'       => 10.00,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
        ]);
        $this->addLineItems($inv2, $productList);
        $createdInvoices[] = $inv2;

        // 3. Partial invoice — with a payment
        $inv3 = Invoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => $nextNumber(),
            'account_id'     => $account->id,
            'owner_id'       => $owner->id,
            'created_by'     => $owner->id,
            'status'         => InvoiceStatus::Partial->value,
            'issue_date'     => now()->subDays(15),
            'due_date'       => now()->addDays(15),
            'currency'       => 'USD',
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_rate'       => 10.00,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
        ]);
        $this->addLineItems($inv3, $productList);
        $createdInvoices[] = $inv3;

        // Record partial payment
        $partialAmount = round($inv3->fresh()->total / 2, 2);
        Payment::create([
            'tenant_id'      => $tenant->id,
            'invoice_id'     => $inv3->id,
            'amount'         => $partialAmount,
            'currency'       => 'USD',
            'payment_date'   => now()->subDays(5),
            'method'         => PaymentMethod::BankTransfer->value,
            'reference_number' => 'REF-' . strtoupper(substr(md5($inv3->id), 0, 8)),
            'status'         => PaymentStatus::Completed->value,
            'created_by'     => $owner->id,
        ]);
        $inv3->update(['amount_paid' => $partialAmount]);

        // 4. Paid invoice — with full payment
        $inv4 = Invoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => $nextNumber(),
            'account_id'     => $accounts->get(1, $account)->id,
            'owner_id'       => $owner->id,
            'created_by'     => $owner->id,
            'status'         => InvoiceStatus::Paid->value,
            'issue_date'     => now()->subDays(30),
            'due_date'       => now()->subDays(2),
            'paid_at'        => now()->subDays(2),
            'currency'       => 'USD',
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_rate'       => 10.00,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
        ]);
        $this->addLineItems($inv4, $productList);
        $createdInvoices[] = $inv4;

        $inv4Fresh = $inv4->fresh();
        Payment::create([
            'tenant_id'      => $tenant->id,
            'invoice_id'     => $inv4->id,
            'amount'         => $inv4Fresh->total,
            'currency'       => 'USD',
            'payment_date'   => now()->subDays(2),
            'method'         => PaymentMethod::CreditCard->value,
            'reference_number' => 'CC-' . strtoupper(substr(md5($inv4->id . 'cc'), 0, 8)),
            'status'         => PaymentStatus::Completed->value,
            'created_by'     => $owner->id,
        ]);
        $inv4->update(['amount_paid' => $inv4Fresh->total]);

        // 5. Overdue invoice
        $inv5 = Invoice::create([
            'tenant_id'      => $tenant->id,
            'invoice_number' => $nextNumber(),
            'account_id'     => $account->id,
            'owner_id'       => $users->get(1, $owner)->id,
            'created_by'     => $owner->id,
            'status'         => InvoiceStatus::Overdue->value,
            'issue_date'     => now()->subDays(45),
            'due_date'       => now()->subDays(15),
            'currency'       => 'USD',
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_rate'       => 10.00,
            'tax_amount'     => 0,
            'total'          => 0,
            'amount_paid'    => 0,
        ]);
        $this->addLineItems($inv5, $productList);
        $createdInvoices[] = $inv5;

        return $createdInvoices;
    }

    private function addLineItems(Invoice $invoice, $products): void
    {
        $subtotal = 0;

        foreach ($products->take(2) as $i => $product) {
            $qty      = rand(1, 3);
            $price    = $product->price ?? 299.00;
            $lineSubt = round($qty * $price, 2);
            $subtotal += $lineSubt;

            InvoiceLineItem::create([
                'invoice_id'      => $invoice->id,
                'product_id'      => $product->id,
                'description'     => $product->name,
                'quantity'        => $qty,
                'unit_price'      => $price,
                'discount_percent'=> 0,
                'subtotal'        => $lineSubt,
                'sort_order'      => $i,
            ]);
        }

        // If no products, add a generic line item
        if ($products->isEmpty()) {
            $subtotal = 500.00;
            InvoiceLineItem::create([
                'invoice_id'      => $invoice->id,
                'product_id'      => null,
                'description'     => 'Professional Services',
                'quantity'        => 5,
                'unit_price'      => 100.00,
                'discount_percent'=> 0,
                'subtotal'        => 500.00,
                'sort_order'      => 0,
            ]);
        }

        $taxAmount = round($subtotal * ($invoice->tax_rate / 100), 2);
        $total     = $subtotal + $taxAmount;

        $invoice->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $total,
        ]);
    }

    private function createExpenses(
        Tenant $tenant,
        $users,
        $accounts,
        $opportunities
    ): void {
        $owner = $users->first();
        $opp   = $opportunities->first();

        $expenses = [
            [
                'category'     => ExpenseCategory::Travel->value,
                'description'  => 'Flight to client site — Q1 sales meeting',
                'amount'       => 480.00,
                'expense_date' => now()->subDays(12),
                'status'       => ExpenseStatus::Approved->value,
                'approved_by'  => $users->get(1, $owner)->id,
                'approved_at'  => now()->subDays(10),
            ],
            [
                'category'     => ExpenseCategory::Meals->value,
                'description'  => 'Client dinner — enterprise deal discussion',
                'amount'       => 135.50,
                'expense_date' => now()->subDays(8),
                'status'       => ExpenseStatus::Approved->value,
                'approved_by'  => $users->get(1, $owner)->id,
                'approved_at'  => now()->subDays(7),
            ],
            [
                'category'     => ExpenseCategory::Software->value,
                'description'  => 'Annual SaaS tool subscription for project delivery',
                'amount'       => 299.00,
                'expense_date' => now()->subDays(3),
                'status'       => ExpenseStatus::Pending->value,
                'approved_by'  => null,
                'approved_at'  => null,
            ],
            [
                'category'     => ExpenseCategory::Accommodation->value,
                'description'  => 'Hotel stay — 2 nights for on-site workshop',
                'amount'       => 320.00,
                'expense_date' => now()->subDays(1),
                'status'       => ExpenseStatus::Rejected->value,
                'approved_by'  => $users->get(1, $owner)->id,
                'approved_at'  => now(),
            ],
        ];

        foreach ($expenses as $data) {
            Expense::create(array_merge($data, [
                'tenant_id'      => $tenant->id,
                'user_id'        => $owner->id,
                'opportunity_id' => $opp?->id,
                'account_id'     => null,
                'currency'       => 'USD',
                'created_by'     => $owner->id,
            ]));
        }
    }
}
