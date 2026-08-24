<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    /**
     * Valeur d'un champ dans la langue courante, avec repli sur le français.
     *
     * La convention est simple : `description` porte le texte français,
     * `description_ar` sa traduction. Une traduction vide — chaîne blanche
     * comprise — laisse passer le texte d'origine, pour qu'une fiche à moitié
     * traduite reste lisible plutôt que trouée.
     */
    public function translated(string $attribute, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === config('localization.default', 'fr')) {
            return $this->{$attribute};
        }

        $translation = $this->{$attribute.'_'.$locale} ?? null;

        return filled($translation) ? $translation : $this->{$attribute};
    }
}
