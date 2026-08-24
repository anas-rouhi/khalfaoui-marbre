# Photos et identité visuelle du site

## Logo — `brand/`

| Fichier | Usage |
| --- | --- |
| `brand/khalfaoui-marbre-logo.svg` | Signature complète (emblème + KHALFAOUI MARBRE S.A.R.L). En-tête et pied de page du site, barre et page de connexion du back-office. |
| `brand/khalfaoui-marbre-mark.svg` | Emblème seul. Écrans étroits (< 640 px) et favicon. |

Les deux fichiers sont **dessinés en blanc et vert** : ils se posent sur un fond
sombre. Sur un fond clair, les composants les placent automatiquement sur un
socle sombre (c'est le cas dans le back-office Filament).

Les chemins sont déclarés une seule fois dans `config/company.php`
(`logo` et `logo_mark`) : pour changer de logo, remplacez le fichier en gardant
le même nom, ou modifiez ces deux lignes.

> `logo-khalfaoui-marbre.png` est la **photo de l'enseigne** du dépôt, conservée
> comme référence. Ce n'est pas un fichier logo exploitable : perspective de
> prise de vue, ciel et environnement visibles, et ancien numéro de téléphone
> imprimé dessus. Si vous récupérez le fichier vectoriel d'origine auprès de
> votre enseigniste, déposez-le dans `brand/` et mettez `config/company.php`
> à jour — rien d'autre à modifier.

## `hero/`

| Fichier | Usage |
| --- | --- |
| `hero-cuisine-granit.jpg` | Image plein écran de la page d'accueil. 1920 × 1280 px min., paysage. Le texte occupe la moitié gauche : privilégiez un cliché dont le sujet est à droite. |

## `catalogue/`

Une photo par référence, format **4:3** (1200 × 900 px). Le nom du fichier est
repris dans `database/seeders/MarbreSeeder.php`.

`marbre-carrara.jpg` · `marbre-calacatta-gold.jpg` · `marbre-nero-marquina.jpg` ·
`marbre-emperador-dark.jpg` · `marbre-crema-marfil.jpg` · `marbre-bardiglio-gris.jpg` ·
`marbre-beige-zenata.jpg` · `marbre-noir-khenifra.jpg` · `granit-noir-absolu.jpg` ·
`granit-perle-blanche.jpg` · `granit-emerald-pearl.jpg` · `granit-gris-tarn.jpg` ·
`travertin-beige.jpg` · `travertin-noyer.jpg`

## `realisations/`

Photos de chantiers, format paysage (1600 × 1200 px). Chaque réalisation a une
photo de couverture et, éventuellement, des vues de galerie.

| Réalisation | Couverture | Galerie |
| --- | --- | --- |
| Villa de luxe à Anfa | `anfa-calacatta-cuisine.jpg` | `anfa-calacatta-sdb.jpg`, `anfa-calacatta-detail.jpg` |
| Appartement Gauthier | `gauthier-carrara-sol.jpg` | `gauthier-carrara-sdb.jpg` |
| Riad à Marrakech | `marrakech-travertin-sdb.jpg` | `marrakech-travertin-patio.jpg` |
| Hôtel boutique | `hotel-facade-travertin.jpg` | `hotel-facade-detail.jpg`, `hotel-facade-entree.jpg` |
| Villa Souissi à Rabat | `rabat-escalier-marbre.jpg` | `rabat-villa-nuit.jpg`, `rabat-villa-exterieur.jpg` |
| Résidence Prestige | `bouskoura-piscine-pierre.jpg` | `bouskoura-terrasse-nuit.jpg` |

### Comparateur avant / après

Le site sait afficher un comparateur à volet coulissant entre la photo
« avant travaux » et la photo finie d'un chantier.

**Aucune photo « avant » n'est livrée avec le site.** Une comparaison n'a de
sens que si les deux clichés montrent le même endroit, sous le même angle :
cela ne s'improvise pas avec des images d'illustration. Prenez le réflexe de
photographier chaque chantier **avant de commencer**, depuis le point de vue
que vous utiliserez pour la photo finale.

Le comparateur apparaît automatiquement dès qu'une réalisation possède les
deux photos, et reste invisible tant que ce n'est pas le cas. L'envoi se fait
depuis `/admin` → Réalisations → section « Photo avant travaux ».

## Remplacer les photos

Deux méthodes, au choix :

1. **Par le back-office** (recommandé au quotidien) : `/admin` → Marbres &
   granits ou Réalisations → téléverser. Les fichiers partent alors dans
   `storage/app/public/` et le site les sert via `/storage/…`.
2. **Par fichier** : écrasez le fichier ici en gardant exactement le même nom.
   Aucune ligne de code n'est à modifier, et un nouveau `php artisan db:seed`
   ne détruira rien.

## Bon à savoir

- Les photos actuelles sont des **clichés d'illustration** libres de droits
  (licence Unsplash), en attendant vos propres réalisations. Remplacez-les :
  vos vrais chantiers valent bien mieux qu'une banque d'images.
- Compressez avant mise en ligne, viser moins de 300 Ko par photo. Toutes les
  images sont chargées en `loading="lazy"` sauf celle du hero.
- Si un fichier manque, la carte affiche proprement « Photo à venir » plutôt
  qu'une image cassée.
