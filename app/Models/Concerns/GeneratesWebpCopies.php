<?php

namespace App\Models\Concerns;

use App\Support\ImageOptimizer;
use App\Support\MediaPath;

/**
 * Produit la copie WebP d'une photo dès qu'elle est enregistrée depuis le
 * back-office : le site sert ainsi la version légère sans intervention.
 *
 * Le modèle déclare ses colonnes d'image via `webpImageAttributes()`.
 */
trait GeneratesWebpCopies
{
    /**
     * @return array<int, string>
     */
    abstract protected function webpImageAttributes(): array;

    protected static function bootGeneratesWebpCopies(): void
    {
        static::saved(function (self $model) {
            foreach ($model->webpImageAttributes() as $attribute) {
                $path = $model->{$attribute};

                if (! $model->wasChanged($attribute) && ! $model->wasRecentlyCreated) {
                    continue;
                }

                if (blank($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    continue;
                }

                ImageOptimizer::toWebp(MediaPath::absolutePath($path));
            }
        });
    }
}
