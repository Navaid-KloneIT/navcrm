<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebFormSubmission extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'web_form_id',
        'data',
        'ip_address',
        'user_agent',
        'is_converted',
        'lead_id',
    ];

    protected function casts(): array
    {
        return [
            'data'         => 'array',
            'is_converted' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(WebForm::class, 'web_form_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
