# Back-office KHALFAOUI MARBRE

Panneau d'administration **FilamentPHP v3.3** monté sur `/admin`.
Il permet de gérer le catalogue, les réalisations et les demandes de devis
sans toucher au code ni à la base de données.

---

## 1. Ce qui a déjà été fait

Ces étapes sont **déjà exécutées** dans ce dépôt — elles sont listées pour
pouvoir reproduire l'installation sur un autre poste ou sur le serveur.

```bash
# 1. Le paquet (v3.3 : compatible Laravel 13, contrairement aux v3.0–3.2)
composer require filament/filament:"^3.3"

# 2. Le panneau « admin »
php artisan filament:install --panels

# 3. Colonne d'autorisation + champs du catalogue
php artisan migrate

# 4. Lien public vers les fichiers téléversés (indispensable pour les photos)
php artisan storage:link
```

## 2. Créer le compte du gérant

```bash
php artisan marbre:admin
```

La commande demande le nom, l'e-mail et le mot de passe, puis crée un compte
avec les droits d'administration. Elle accepte aussi les options directement :

```bash
php artisan marbre:admin --name="Khalfaoui" --email="contact@exemple.com" --password="…"
```

Lancée sur une adresse déjà connue, elle **promeut** le compte existant : c'est
aussi le moyen de redonner l'accès à quelqu'un.

Connexion ensuite sur **`/admin/login`**.

### Modifier son compte au quotidien

Une fois connecté, le menu en haut à droite ouvre la page **Profil**
(`/admin/profile`) : nom, adresse e-mail et mot de passe s'y modifient
directement, sans repasser par la ligne de commande. Laisser le champ mot de
passe vide conserve l'ancien.

> **Sécurité** — seuls les comptes dont la colonne `users.is_admin` vaut `true`
> peuvent ouvrir le back-office. Les visiteurs qui s'inscrivent sur le site
> n'y ont aucun accès. C'est la méthode `User::canAccessPanel()` qui l'impose ;
> sans elle, Filament bloquerait tout le monde en production.

## 3. Les trois écrans

### Catalogue › Marbres & granits (`ProductResource`)

| Champ | Rôle |
| --- | --- |
| Nom commercial | Titre de la fiche. L'identifiant URL se remplit tout seul à la création. |
| Famille | Marbre, Granit, Travertin… Une nouvelle famille se crée depuis le champ lui-même. |
| Couleur affichée | Texte libre montré sur la carte (« Noir veiné blanc »). |
| **Teinte** | Liste fermée : commande le **filtre couleur** du site. |
| **Usages** | Cases à cocher : commandent le **filtre d'usage** du site. |
| Prix indicatif | En DH/m². Alimente l'estimateur de devis. Vide ⇒ « Prix sur demande ». |
| Mettre en avant | Remonte la référence en tête du catalogue. |
| Photographies | Une ou plusieurs images ; cocher « Photo principale » désigne celle de la carte. |

Les photos partent dans `storage/app/public/products/` et sont servies via
`/storage/products/…`.

### Catalogue › Réalisations (`ProjectResource`)

Titre, type de bien (Villa, Appartement, Résidence, Hôtel, Bureau, Showroom),
localisation, année, description et photo de couverture.
Les lignes se **réordonnent par glisser-déposer** dans le tableau ; l'ordre
obtenu est celui du portfolio public.

La section **Photo « avant travaux »** alimente le comparateur à volet
coulissant du site. Elle est facultative : le comparateur n'apparaît que pour
les réalisations qui possèdent **à la fois** une photo avant et une photo de
couverture. Aucune photo « avant » n'est livrée avec le site — une comparaison
n'a de valeur que si les deux clichés montrent le même endroit sous le même
angle. Prenez donc la photo « avant » depuis le point de vue que vous
utiliserez pour la photo finie.

La section **Galerie du chantier** permet d'ajouter autant de vues que
souhaité, chacune avec une légende. Le visiteur les feuillette dans la
visionneuse (flèches, pastilles, touches ← et →) après avoir cliqué sur une
réalisation. Les vues se réordonnent aussi par glisser-déposer.

### Clients › Demandes de devis (`DevisResource`)

- Onglets **Toutes / À rappeler / Contacté / Terminé**, avec compteurs.
- Pastille dans le menu : nombre de demandes encore à rappeler.
- Badges de statut : `À rappeler` **ambre**, `Contacté` **bleu**, `Terminé` **vert**.
- Boutons **WhatsApp** et **Appeler** sur chaque ligne. Le message WhatsApp est
  pré-rédigé avec le nom du client, le matériau et la surface demandés.
- Le menu « ⋮ » permet de passer une demande à « Contacté » ou « Terminé » en un clic.
- Le tableau se rafraîchit tout seul toutes les 60 secondes.
- **Aucune création manuelle** : les demandes ne viennent que du formulaire public.
  Les informations client sont en lecture seule ; seul le statut est modifiable.

Les numéros saisis par les clients (`06 61 21 94 09`, `0661219409`,
`+212661219409`, `00212…`) sont tous normalisés en `212…` pour les liens
`wa.me` et `tel:`.

### Réglages › Tarifs de pose (`InstallationRateResource`)

Le prix de la **main d'œuvre au m²**, par type de pose. C'est la seconde moitié
de l'estimation affichée aux visiteurs : `fourniture + pose`.

| Type de pose | Tarif de départ |
| --- | --- |
| Plan de travail / Cuisine | 220 DH/m² |
| Sol | 120 DH/m² |
| Salle de bain | 180 DH/m² |
| Escalier | 300 DH/m² |
| Façade | 260 DH/m² |

- Le tarif se corrige **directement dans le tableau**, sans ouvrir de fiche ;
  l'estimateur du site l'applique au rechargement suivant.
- L'interrupteur **Actif** retire un type de pose de l'estimateur sans le
  supprimer. Un tarif désactivé reste appliqué aux demandes déjà reçues.
- Les lignes se réordonnent par glisser-déposer : c'est l'ordre des boutons
  dans l'estimateur.
- Sans aucune ligne, l'estimateur retombe sur **150 DH/m²**
  (`InstallationRate::FALLBACK_RATE`).

Le montant est **toujours recalculé sur le serveur** à la réception d'une
demande : une valeur trafiquée dans le navigateur n'a aucun effet sur le
montant enregistré.

## 4. Site bilingue français / arabe

Le site public se consulte en **français** ou en **arabe**, au choix du visiteur
via le sélecteur `FR | AR` de la barre de navigation. La langue est mémorisée
dans sa session ; les adresses des pages restent identiques.

### Textes de l'interface

Boutons, titres de sections, libellés du formulaire de devis, pied de page…
tout provient de deux dictionnaires :

```
lang/fr.json    ← français
lang/ar.json    ← arabe
```

Les deux fichiers portent exactement les **mêmes clés** (un test le vérifie à
chaque exécution de la suite). Pour corriger une formulation, modifiez la
valeur puis relancez `npm run build`.

### Contenu du catalogue

Chaque fiche possède des champs arabes **facultatifs** :

| Écran | Champs arabes |
| --- | --- |
| Marbres & granits | Nom, Couleur, Description |
| Réalisations | Titre, Description |

Laissés vides, le site affiche le texte français : une fiche à moitié traduite
reste donc parfaitement lisible. Vous pouvez traduire progressivement, en
commençant par vos références phares.

### À savoir

- Le **back-office reste en français**, quelle que soit la langue choisie sur
  le site public.
- Les numéros de téléphone et les montants s'affichent en chiffres occidentaux
  dans les deux langues, avec une espace comme séparateur de milliers.
- En arabe, la page bascule en écriture de droite à gauche (`dir="rtl"`) et
  utilise la police Cairo, l'arabe n'étant pas couvert par les polices latines
  du site.

## 5. Points d'attention

- **Vocabulaire des usages** : un tarif de pose se rattache à un usage du
  catalogue par sa clé (`cuisine`, `sol`, `salle-de-bain`, `facade`,
  `escalier`) — avec des **traits d'union**, jamais des tirets bas. Le champ
  « Type de pose » est une liste fermée pour éviter toute faute de frappe.
- **Teintes et usages** : les clés stockées en base doivent rester celles
  définies dans `App\Models\Product::COLOR_FAMILIES` et `::APPLICATIONS`.
  Elles sont partagées avec `CatalogueSection.vue` et `DevisSection.vue` ;
  changer une clé d'un seul côté ferait disparaître la pierre des filtres.
- **Deux origines d'images cohabitent** : les chemins livrés avec le site
  (`/images/catalogue/…`) et les fichiers téléversés (`products/…`).
  `App\Support\MediaPath` résout les deux, côté site comme côté back-office.
- **Langue** : `APP_LOCALE=fr` — l'interface Filament s'affiche en français.
- En production, penser à `php artisan config:cache` et
  `php artisan filament:optimize` après déploiement.
