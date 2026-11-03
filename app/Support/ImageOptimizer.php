<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Produit une copie WebP, plus légère, de chaque photo JPEG ou PNG.
 *
 * La copie est posée à côté de l'original (« x.jpg » → « x.webp ») :
 * MediaPath::url() la sert dès qu'elle existe, sans rien changer en base.
 * L'original reste en place pour le PDF et comme source de régénération.
 */
class ImageOptimizer
{
    /** Largeur maximale servie sur le site, écrans Retina compris. */
    public const MAX_WIDTH = 1920;

    public const QUALITY = 78;

    public static function webpPath(string $source): ?string
    {
        if (! preg_match('/\.(jpe?g|png)$/i', $source)) {
            return null;
        }

        return preg_replace('/\.(jpe?g|png)$/i', '.webp', $source);
    }

    /**
     * Crée (ou rafraîchit) la copie WebP. Ne lève jamais d'exception : une
     * conversion ratée laisse simplement l'original servi tel quel.
     *
     * @return string|null Chemin de la copie WebP, ou null si elle n'a pas pu être produite.
     */
    public static function toWebp(string $source): ?string
    {
        $target = self::webpPath($source);

        if ($target === null || ! is_file($source) || ! function_exists('imagewebp')) {
            return null;
        }

        if (is_file($target) && filemtime($target) >= filemtime($source)) {
            return $target;
        }

        try {
            $image = match (@getimagesize($source)[2] ?? null) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
                IMAGETYPE_PNG => @imagecreatefrompng($source),
                default => false,
            };

            if (! $image) {
                return null;
            }

            if (imagesx($image) > self::MAX_WIDTH) {
                $image = imagescale($image, self::MAX_WIDTH);
            }

            // Transparence des PNG conservée.
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            imagewebp($image, $target, self::QUALITY);
        } catch (Throwable $e) {
            Log::warning("Conversion WebP impossible : $source", ['exception' => $e]);

            return null;
        }

        return is_file($target) ? $target : null;
    }
}
