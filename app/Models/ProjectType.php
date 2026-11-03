<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Type de projet d'une réalisation (Villa, Appartement, Hôtel…) : bouton de
 * filtre de la section Réalisations.
 *
 * Les réalisations s'y rattachent par la clé (`projects.category`), qui ne
 * change plus une fois créée.
 */
class ProjectType extends Model
{
    use HasTranslations;

    protected $guarded = [];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'category', 'key');
    }

    /** Options « clé => nom » pour les listes du back-office. */
    public static function options(): array
    {
        return static::query()->ordered()->pluck('name', 'key')->all();
    }
}
