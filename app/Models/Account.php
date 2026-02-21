<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use BelongsToTenant, Filterable, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'industry',
        'website',
        'phone',
        'email',
        'annual_revenue',
        'employee_count',
        'tax_id',
        'description',
        'parent_id',
        'owner_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'annual_revenue' => 'decimal:2',
            'employee_count' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'account_contact')
            ->withPivot('role', 'is_primary')
            ->withTimestamps();
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /* ── Customer Success ───────────────────────────────────────────── */

    public function onboardingPipelines(): HasMany
    {
        return $this->hasMany(OnboardingPipeline::class);
    }

    public function healthScores(): HasMany
    {
        return $this->hasMany(HealthScore::class)->orderByDesc('calculated_at');
    }

    public function latestHealthScore(): HasOne
    {
        return $this->hasOne(HealthScore::class)->latestOfMany('calculated_at');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
