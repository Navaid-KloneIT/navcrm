<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'department',
        'description',
        'linkedin_url',
        'twitter_handle',
        'facebook_url',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'source',
        'owner_id',
        'created_by',
        'portal_password',
        'portal_active',
    ];

    protected $hidden = [
        'portal_password',
    ];

    protected $casts = [
        'portal_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_contact')
            ->withPivot('role', 'is_primary')
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function relatedContacts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'contact_relationships', 'contact_id', 'related_contact_id')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function relatedFrom(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'contact_relationships', 'related_contact_id', 'contact_id')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
