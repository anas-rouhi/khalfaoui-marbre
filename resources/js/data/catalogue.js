/**
 * Données de démonstration.
 *
 * Elles ne servent que si la base est vide : dès que `php artisan db:seed` a
 * tourné, HomeController fournit les vraies références et ce fichier n'est plus
 * utilisé. Pour changer les visuels, remplacez les fichiers dans
 * `public/images/catalogue/` et `public/images/realisations/` en conservant les
 * mêmes noms, puis lancez `php artisan marbre:optimize-images` pour régénérer
 * les copies WebP servies ici — aucun code à modifier.
 *
 * Les identifiants « demo-… » ne correspondent à aucune ligne en base :
 * DevisSection ne les envoie jamais comme `product_id`.
 */

export const FALLBACK_PRODUCTS = [
    {
        id: 'demo-granit-noir-absolu',
        categoryKey: 'granit',
        name: 'Granit Noir Absolu',
        color: 'Noir profond',
        colorFamily: 'noir',
        origin: 'Maroc',
        finish: 'Poli miroir',
        applications: ['cuisine', 'sol', 'facade'],
        pricePerM2: 950,
        image: '/images/catalogue/granit-noir-absolu.webp',
        description: "Notre référence la plus demandée pour les plans de travail : dense, non poreuse et d'un entretien simple.",
    },
    {
        id: 'demo-granit-perle-blanche',
        categoryKey: 'granit',
        name: 'Granit Perle Blanche',
        color: 'Blanc moucheté gris',
        colorFamily: 'blanc',
        origin: 'Maroc',
        finish: 'Poli',
        applications: ['cuisine', 'sol', 'salle-de-bain'],
        pricePerM2: 780,
        image: '/images/catalogue/granit-perle-blanche.webp',
        description: 'Un granit clair au grain régulier qui agrandit visuellement les cuisines.',
    },
    {
        id: 'demo-granit-emerald-pearl',
        categoryKey: 'granit',
        name: 'Granit Emerald Pearl',
        color: 'Vert nacré',
        colorFamily: 'vert',
        origin: 'Norvège',
        finish: 'Poli miroir',
        applications: ['cuisine', 'facade'],
        pricePerM2: 1450,
        image: '/images/catalogue/granit-emerald-pearl.webp',
        description: 'Des reflets verts nacrés qui changent avec la lumière.',
    },
    {
        id: 'demo-granit-gris-tarn',
        categoryKey: 'granit',
        name: 'Granit Gris Tarn',
        color: 'Gris clair',
        colorFamily: 'gris',
        origin: 'Maroc',
        finish: 'Adouci',
        applications: ['sol', 'escalier', 'facade'],
        pricePerM2: 690,
        image: '/images/catalogue/granit-gris-tarn.webp',
        description: 'Le choix de référence pour les escaliers et circulations intensives.',
    },
    {
        id: 'demo-marbre-carrara',
        categoryKey: 'marbre',
        name: 'Marbre Carrara Blanc',
        color: 'Blanc veiné gris',
        colorFamily: 'blanc',
        origin: 'Italie',
        finish: 'Poli',
        applications: ['salle-de-bain', 'sol', 'cuisine'],
        pricePerM2: 1650,
        image: '/images/catalogue/marbre-carrara.webp',
        description: 'Le marbre italien intemporel, veinage doux et lumineux.',
    },
    {
        id: 'demo-marbre-marquina',
        categoryKey: 'marbre',
        name: 'Marbre Noir Marquina',
        color: 'Noir veiné blanc',
        colorFamily: 'noir',
        origin: 'Espagne',
        finish: 'Poli',
        applications: ['salle-de-bain', 'sol', 'facade'],
        pricePerM2: 1890,
        image: '/images/catalogue/marbre-nero-marquina.webp',
        description: 'Un noir profond traversé de veines blanches franches.',
    },
    {
        id: 'demo-marbre-beige-zenata',
        categoryKey: 'marbre',
        name: 'Marbre Beige Zenata',
        color: 'Beige sable',
        colorFamily: 'beige',
        origin: 'Maroc',
        finish: 'Adouci',
        applications: ['sol', 'escalier', 'facade'],
        pricePerM2: 720,
        image: '/images/catalogue/marbre-beige-zenata.webp',
        description: 'Marbre marocain chaleureux, extrait localement.',
    },
    {
        id: 'demo-travertin-beige',
        categoryKey: 'travertin-pierre',
        name: 'Travertin Beige Classique',
        color: 'Beige crème',
        colorFamily: 'beige',
        origin: 'Turquie',
        finish: 'Brossé',
        applications: ['sol', 'facade', 'salle-de-bain'],
        pricePerM2: 640,
        image: '/images/catalogue/travertin-beige.webp',
        description: 'Pierre naturelle idéale pour terrasses, patios et façades.',
    },
];

export const FALLBACK_PROJECTS = [
    {
        id: 'demo-villa-emerald',
        title: 'Villa contemporaine — plan de travail Emerald Pearl',
        category: 'Villa',
        categoryKey: 'villa',
        location: 'Californie, Casablanca',
        year: 2024,
        description: "Cuisine ouverte en granit Emerald Pearl poli miroir, îlot central et retour bar réalisés d'une seule pièce.",
        image: '/images/catalogue/granit-emerald-pearl.webp',
    },
    {
        id: 'demo-escalier-gris',
        title: 'Escalier monumental en granit gris',
        category: 'Résidence',
        categoryKey: 'residence',
        location: 'Lahraouiyine, Casablanca',
        year: 2024,
        description: 'Vingt-deux marches avec nez arrondi et contremarches assorties, posées sur un escalier tournant.',
        image: '/images/realisations/rabat-escalier-marbre.webp',
    },
    {
        id: 'demo-cuisine-noire',
        title: 'Cuisine noire laquée — résidence de standing',
        category: 'Appartement',
        categoryKey: 'appartement',
        location: 'Sidi Maârouf, Casablanca',
        year: 2025,
        description: "Plan de travail et crédence en granit noir absolu, découpe d'évier sous plan et chants polis.",
        image: '/images/hero/hero-cuisine-granit.webp',
    },
    {
        id: 'demo-cuisine-perle',
        title: 'Cuisine en U — granit Perle Blanche',
        category: 'Villa',
        categoryKey: 'villa',
        location: 'Bouskoura, Casablanca',
        year: 2025,
        description: "Plan de travail en U avec plinthes assorties et découpe d'évier intégrée.",
        image: '/images/realisations/anfa-calacatta-cuisine.webp',
    },
    {
        id: 'demo-comptoir-bar',
        title: 'Comptoir bar et plan de travail noir',
        category: 'Appartement',
        categoryKey: 'appartement',
        location: 'Ain Sebaâ, Casablanca',
        year: 2025,
        description: 'Comptoir bar avec joue pleine hauteur et plan de travail continu, finition poli miroir.',
        image: '/images/catalogue/granit-noir-absolu.webp',
    },
    {
        id: 'demo-facade-showroom',
        title: 'Habillage de façade et clôture en granit',
        category: 'Showroom',
        categoryKey: 'showroom',
        location: 'Route 1033, Lahraouiyine',
        year: 2023,
        description: 'Notre propre dépôt : alternance de dalles noires et grises polies en habillage de clôture.',
        image: '/images/realisations/hotel-facade-entree.webp',
    },
];

/**
 * Filtres de démonstration, utilisés avec FALLBACK_PRODUCTS seulement.
 * En temps normal, teintes, usages et familles viennent du back-office
 * (Réglages › Teintes, Réglages › Usages & tarifs, Catalogue › Familles de
 * matière). Les libellés sont traduits par la page via les clés `colour.*`,
 * `application.*` et `family.*`.
 */
export const FALLBACK_COLOR_FAMILIES = [
    { key: 'noir', swatch: '#141519' },
    { key: 'blanc', swatch: '#eceae5' },
    { key: 'gris', swatch: '#8b8e94' },
    { key: 'beige', swatch: '#cbb094' },
    { key: 'brun', swatch: '#6b4a33' },
    { key: 'vert', swatch: '#1e9d6b' },
];

export const FALLBACK_APPLICATIONS = ['sol', 'cuisine', 'salle-de-bain', 'facade', 'escalier'].map((key) => ({ key }));

export const FALLBACK_CATEGORIES = ['marbre', 'granit', 'travertin-pierre'].map((key) => ({ key }));

/** Types de projet de démonstration, utilisés avec FALLBACK_PROJECTS seulement (libellés : clés `project_type.*`). */
export const FALLBACK_PROJECT_TYPES = ['villa', 'appartement', 'residence', 'showroom'].map((key) => ({ key }));
