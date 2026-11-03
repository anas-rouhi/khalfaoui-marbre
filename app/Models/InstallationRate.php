<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Usage d'une pierre (Sol, Cuisine, Escalier…) et son tarif de pose.
 *
 * C'est la liste unique des usages du site : cases à cocher des fiches
 * produit, boutons du filtre catalogue et choix de l'estimateur de devis.
 * `is_active` ne concerne que l'estimateur ; le filtre du catalogue montre
 * tout usage porté par au moins une fiche.
 */
class InstallationRate extends Model
{
    use HasTranslations;

    /**
     * Tarif appliqué lorsqu'aucune ligne ne correspond à l'application
     * demandée — par exemple si le gérant vient de la désactiver alors qu'un
     * visiteur avait déjà le formulaire ouvert.
     */
    public const FALLBACK_RATE = 150.0;

    /** Valeurs de départ, reprises des tarifs pratiqués jusqu'ici. */
    public const DEFAULTS = [
        ['application' => 'cuisine', 'label' => 'Plan de travail / Cuisine', 'label_ar' => 'سطح مطبخ', 'rate_per_m2' => 220, 'sort_order' => 1],
        ['application' => 'sol', 'label' => 'Sol', 'label_ar' => 'أرضية', 'rate_per_m2' => 120, 'sort_order' => 2],
        ['application' => 'salle-de-bain', 'label' => 'Salle de bain', 'label_ar' => 'حمام', 'rate_per_m2' => 180, 'sort_order' => 3],
        ['application' => 'escalier', 'label' => 'Escalier', 'label_ar' => 'درج', 'rate_per_m2' => 300, 'sort_order' => 4],
        ['application' => 'facade', 'label' => 'Façade', 'label_ar' => 'واجهة', 'rate_per_m2' => 260, 'sort_order' => 5],
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

    /** Options « clé => libellé » pour les listes du back-office. */
    public static function options(): array
    {
        return static::query()->orderBy('sort_order')->orderBy('label')->pluck('label', 'application')->all();
    }

    /**
     * Libellé d'un usage dans la langue voulue, même désactivé ; la clé brute
     * en dernier recours, si l'usage a été supprimé depuis.
     */
    public static function labelFor(?string $application, ?string $locale = null): ?string
    {
        if (blank($application)) {
            return null;
        }

        return static::query()->firstWhere('application', $application)?->translated('label', $locale)
            ?? collect(self::DEFAULTS)->firstWhere('application', $application)['label']
            ?? $application;
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
