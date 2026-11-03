<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Famille de matière (Marbre, Granit, Travertin…) : troisième filtre du
 * catalogue. Le site identifie une famille par son slug.
 */
class Category extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
