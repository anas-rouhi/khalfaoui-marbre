<?php

namespace App\Support;

use ArPHP\I18N\Arabic;

/**
 * Prépare le texte arabe pour dompdf.
 *
 * dompdf ne sait ni lier les lettres arabes entre elles ni inverser le sens
 * de lecture : sans traitement, « رخام » s'afficherait en lettres isolées et
 * de gauche à droite. ar-php convertit le texte en formes de présentation
 * liées, déjà rangées dans l'ordre visuel.
 *
 * Conséquence : dompdf ne doit plus couper ces lignes lui-même — il le ferait
 * dans le mauvais ordre. Les textes longs sont donc découpés ici, à une
 * largeur fournie par le gabarit.
 */
class PdfText
{
    private static ?Arabic $arabic = null;

    public static function hasArabic(?string $text): bool
    {
        return $text !== null && preg_match('/\p{Arabic}/u', $text) === 1;
    }

    /**
     * Texte prêt à l'affichage. Les lignes éventuelles sont séparées par
     * « \n » : le gabarit les convertit en <br>.
     */
    public static function shape(?string $text, int $maxChars = 200): string
    {
        $text = (string) $text;

        if (! self::hasArabic($text)) {
            return $text;
        }

        // Chiffres occidentaux conservés (`$hindo = false`) : les montants
        // doivent rester identiques à ceux affichés sur le site.
        return self::arabic()->utf8Glyphs($text, $maxChars, false);
    }

    private static function arabic(): Arabic
    {
        return self::$arabic ??= new Arabic;
    }
}
