<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'quote_id',
        'opportunity_id',
        'account_id',
        'contact_id',
        'owner_id',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'amount_paid',
        'currency',
        'notes',
        'terms',
        'is_recurring',
        'recurrence',
        'recurrence_end_date',
        'next_invoice_date',
        'parent_invoice_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'              => InvoiceStatus::class,
            'issue_date'          => 'date',
            'due_date'            => 'date',
            'paid_at'             => 'datetime',
            'recurrence_end_date' => 'date',
            'next_invoice_date'   => 'date',
            'subtotal'            => 'decimal:2',
            'discount_value'      => 'decimal:2',
            'discount_amount'     => 'decimal:2',
            'tax_rate'            => 'decimal:2',
            'tax_amount'          => 'decimal:2',
            'total'               => 'decimal:2',
            'amount_paid'         => 'decimal:2',
            'is_recurring'        => 'boolean',
        ];
    }

    public function getAmountDueAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function childInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date');
    }
}
