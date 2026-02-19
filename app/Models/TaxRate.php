<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'country',
        'region',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate'       => 'decimal:2',
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }
}
