<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'po_number',
        'vendor_id',
        'status',
        'order_date',
        'expected_date',
        'received_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => PurchaseOrderStatus::class,
            'order_date'   => 'date',
            'expected_date' => 'date',
            'received_date' => 'date',
            'subtotal'     => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'total_amount' => 'decimal:2',
            'approved_at'  => 'datetime',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ── Computed ────────────────────────────────────────────────────── */

    public function getItemsTotalAttribute(): float
    {
        return (float) $this->items()->sum('total');
    }
}
