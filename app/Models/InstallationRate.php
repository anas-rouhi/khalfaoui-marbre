<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InstallationRate extends Model
{
    /**
     * Tarif appliqué lorsqu'aucune ligne ne correspond à l'application
     * demandée — par exemple si le gérant vient de la désactiver alors qu'un
     * visiteur avait déjà le formulaire ouvert.
     */
    public const FALLBACK_RATE = 150.0;

    /** Valeurs de départ, reprises des tarifs pratiqués jusqu'ici. */
    public const DEFAULTS = [
        ['application' => 'cuisine', 'label' => 'Plan de travail / Cuisine', 'rate_per_m2' => 220, 'sort_order' => 1],
        ['application' => 'sol', 'label' => 'Sol', 'rate_per_m2' => 120, 'sort_order' => 2],
        ['application' => 'salle-de-bain', 'label' => 'Salle de bain', 'rate_per_m2' => 180, 'sort_order' => 3],
        ['application' => 'escalier', 'label' => 'Escalier', 'rate_per_m2' => 300, 'sort_order' => 4],
        ['application' => 'facade', 'label' => 'Façade', 'rate_per_m2' => 260, 'sort_order' => 5],
    ];

    protected $guarded = [];

    protected $casts = [
        'rate_per_m2' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** Tarifs proposés au visiteur, dans l'ordre défini par le gérant. */
    public static function active(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Tarif au m² d'une application, quel que soit son statut : une demande
     * déjà envoyée doit rester chiffrable même si la ligne a été désactivée
     * entre-temps.
     */
    public static function rateFor(?string $application): float
    {
        if (blank($application)) {
            return self::FALLBACK_RATE;
        }

        $rate = static::query()->where('application', $application)->value('rate_per_m2');

        return $rate !== null ? (float) $rate : self::FALLBACK_RATE;
    }
}
