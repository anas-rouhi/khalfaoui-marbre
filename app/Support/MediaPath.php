<?php

namespace App\Support;

class MediaPath
{
    /**
     * Transforme un chemin d'image stocké en base en URL affichable.
     *
     * Trois formes coexistent dans le projet et doivent toutes fonctionner :
     *  - une URL absolue (« https://… ») ;
     *  - un chemin public livré avec le site (« /images/catalogue/x.jpg ») ;
     *  - un fichier téléversé depuis le back-office sur le disque « public »
     *    (« products/x.jpg »), servi via le lien symbolique /storage.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Laissé relatif à la racine : valable aussi bien dans les pages du
        // site que dans le back-office, et insensible au changement de domaine.
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    /**
     * URL absolue du fichier, ou null s'il n'est pas (encore) présent.
     *
     * Utilisée par les vignettes du back-office : Filament n'accepte qu'une URL
     * absolue, et un `null` lui fait afficher l'image de remplacement plutôt
     * qu'une image cassée tant que la photo n'a pas été déposée.
     */
    public static function thumbnail(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $absolutePath = str_starts_with($path, '/')
            ? public_path(ltrim($path, '/'))
            : storage_path('app/public/'.$path);

        if (! is_file($absolutePath)) {
            return null;
        }

        return url(static::url($path));
    }
}
