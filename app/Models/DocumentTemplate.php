<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use BelongsToTenant, Filterable;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'type',
        'description',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'      => DocumentType::class,
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'template_id');
    }
}
