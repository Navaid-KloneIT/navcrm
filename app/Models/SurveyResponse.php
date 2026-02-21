<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'survey_id',
        'tenant_id',
        'contact_id',
        'account_id',
        'score',
        'comment',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'score'        => 'integer',
            'responded_at' => 'datetime',
        ];
    }

    /* ── Relationships ──────────────────────────────────────────────── */

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
