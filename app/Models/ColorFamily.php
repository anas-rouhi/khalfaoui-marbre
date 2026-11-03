<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Teinte proposée par le filtre du catalogue (Noir, Blanc, Gris…).
 *
 * Les fiches s'y rattachent par la clé (`products.color_family`), qui ne
 * change plus une fois créée : la renommer orphelinerait les fiches.
 */
class ColorFamily extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'color_family', 'key');
    }

    /** Options « clé => nom » pour les listes du back-office. */
    public static function options(): array
    {
        return static::query()->ordered()->pluck('name', 'key')->all();
    }
}
