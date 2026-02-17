<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'addressable_type',
        'addressable_id',
        'type',
        'label',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
