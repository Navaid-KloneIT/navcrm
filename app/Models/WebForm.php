<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebForm extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'fields',
        'submit_button_text',
        'success_message',
        'redirect_url',
        'assign_to_user_id',
        'assign_by_geography',
        'is_active',
        'total_submissions',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fields'               => 'array',
            'assign_by_geography'  => 'boolean',
            'is_active'            => 'boolean',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WebFormSubmission::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function landingPages(): HasMany
    {
        return $this->hasMany(LandingPage::class);
    }
}
