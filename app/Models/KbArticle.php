<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KbArticle extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'category',
        'body',
        'is_public',
        'is_published',
        'author_id',
        'view_count',
    ];

    protected $casts = [
        'is_public'    => 'boolean',
        'is_published' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (KbArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title) . '-' . Str::random(4);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
