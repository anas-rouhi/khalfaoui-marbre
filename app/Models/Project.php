<?php

namespace App\Models;

use App\Models\Concerns\GeneratesWebpCopies;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use GeneratesWebpCopies, HasTranslations;

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    /** Type de projet ; `category` contient sa clé (« villa », « hotel »…). */
    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'category', 'key');
    }

    /** Réalisations visibles sur le site. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    protected function webpImageAttributes(): array
    {
        return ['cover_image', 'before_image'];
    }
}
