/**
 * Génère `resources/js/ziggy.js` avant chaque build Vite.
 *
 * Le fichier généré est versionné : sur une CI sans PHP (Vercel), on garde
 * simplement la version du dépôt au lieu de faire échouer le build. Les URLs
 * réelles viennent de toute façon de `window.Ziggy` (directive @routes) au
 * runtime ; ce fichier ne sert que de définition statique pour le bundle.
 */
import { spawnSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const target = resolve(root, 'resources/js/ziggy.js');
const relative = 'resources/js/ziggy.js';

const canGenerate =
    existsSync(resolve(root, 'artisan')) &&
    existsSync(resolve(root, 'vendor/tightenco/ziggy'));

if (canGenerate) {
    const result = spawnSync('php', ['artisan', 'ziggy:generate', relative], {
        cwd: root,
        stdio: 'inherit',
        shell: process.platform === 'win32',
    });

    if (result.status === 0) {
        console.log(`[ziggy] ${relative} généré.`);
        process.exit(0);
    }

    console.warn('[ziggy] `php artisan ziggy:generate` a échoué.');
} else {
    console.warn('[ziggy] PHP/vendor indisponible (CI) — génération ignorée.');
}

if (existsSync(target)) {
    console.warn(`[ziggy] Utilisation du ${relative} versionné.`);
    process.exit(0);
}

console.error(
    `[ziggy] ${relative} est introuvable et n'a pas pu être généré.\n` +
        '        Lancez `php artisan ziggy:generate` puis committez le fichier.',
);
process.exit(1);
