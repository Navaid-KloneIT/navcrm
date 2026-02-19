<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'opportunity_id',
        'account_id',
        'user_id',
        'category',
        'description',
        'amount',
        'currency',
        'expense_date',
        'receipt_url',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category'     => ExpenseCategory::class,
            'status'       => ExpenseStatus::class,
            'expense_date' => 'date',
            'approved_at'  => 'datetime',
            'amount'       => 'decimal:2',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
