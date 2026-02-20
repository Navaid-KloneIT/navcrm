<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignatory extends Model
{
    protected $fillable = [
        'document_id',
        'name',
        'email',
        'sign_token',
        'status',
        'viewed_at',
        'signed_at',
        'rejected_at',
        'signature_data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at'   => 'datetime',
            'signed_at'   => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
