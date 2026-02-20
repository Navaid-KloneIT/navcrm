<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToTenant, Filterable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'template_id',
        'account_id',
        'contact_id',
        'opportunity_id',
        'owner_id',
        'document_number',
        'title',
        'type',
        'status',
        'body',
        'file_path',
        'notes',
        'expires_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status'     => DocumentStatus::class,
            'type'       => DocumentType::class,
            'expires_at' => 'date',
            'sent_at'    => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signatories(): HasMany
    {
        return $this->hasMany(DocumentSignatory::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }
}
