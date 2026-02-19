<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount',
        'currency',
        'payment_date',
        'method',
        'reference_number',
        'notes',
        'status',
        'gateway_transaction_id',
        'receipt_sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'payment_date'    => 'date',
            'method'          => PaymentMethod::class,
            'status'          => PaymentStatus::class,
            'receipt_sent_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
