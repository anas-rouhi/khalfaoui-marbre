<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Contenu initial complet du site : catalogue, tarifs indicatifs et portfolio.
 *
 * Le seeder est idempotent (updateOrCreate sur le slug / le titre) : il peut
 * être relancé sans créer de doublons ni écraser les demandes de devis.
 *
 * Les photos sont des fichiers locaux de `public/images/…`. Pour publier vos
 * propres clichés, écrasez simplement le fichier en gardant le même nom :
 * aucune ligne de code n'est à modifier.
 */
class MarbreSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        $this->seedProducts($categories);
        $this->seedProjects();
    }

    /**
     * @return array<string, int>
     */
    private function seedCategories(): array
    {
        $families = [
            ['name' => 'Marbre', 'slug' => 'marbre'],
            ['name' => 'Granit', 'slug' => 'granit'],
            ['name' => 'Travertin & Pierre', 'slug' => 'travertin-pierre'],
        ];

        $ids = [];

        foreach ($families as $family) {
            $ids[$family['slug']] = Category::updateOrCreate(
                ['slug' => $family['slug']],
                $family
            )->id;
        }

        return $ids;
    }

    /**
     * Tarifs 2026 indicatifs, fourniture seule, hors pose — à ajuster depuis
     * le back-office selon les cours et les arrivages.
     *
     * @param  array<string, int>  $categories
     */
    private function seedProducts(array $categories): void
    {
        $products = [
            // ───────────── Marbres importés ─────────────
            [
                'category' => 'marbre',
                'name' => 'Marbre Carrara Blanc',
                'color' => 'Blanc veiné gris',
                'color_family' => 'blanc',
                'origin' => 'Carrare, Italie',
                'finish' => 'Polie',
                'applications' => ['cuisine', 'salle-de-bain', 'sol'],
                'price_per_m2' => 1650,
                'featured' => true,
                'description' => "Le marbre le plus célèbre au monde, extrait des carrières de Toscane depuis l'Antiquité. Son fond blanc laiteux parcouru de veines grises discrètes apporte une lumière incomparable aux plans de travail et aux salles de bain. Chaque tranche est unique.",
                'image' => '/images/catalogue/marbre-carrara.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Calacatta Gold',
                'color' => 'Blanc veiné or',
                'color_family' => 'blanc',
                'origin' => 'Carrare, Italie',
                'finish' => 'Polie',
                'applications' => ['cuisine', 'salle-de-bain'],
                'price_per_m2' => 3200,
                'featured' => true,
                'description' => "La pièce maîtresse de notre collection. Un fond blanc pur traversé de veines dorées et grises spectaculaires, réservé aux projets d'exception. Nous sélectionnons les blocs un par un et proposons la mise en miroir des tranches pour les îlots de cuisine.",
                'image' => '/images/catalogue/marbre-calacatta-gold.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Nero Marquina',
                'color' => 'Noir veiné blanc',
                'color_family' => 'noir',
                'origin' => 'Biscaye, Espagne',
                'finish' => 'Polie',
                'applications' => ['salle-de-bain', 'sol', 'facade', 'cuisine'],
                'price_per_m2' => 1890,
                'featured' => true,
                'description' => "Un noir profond, presque absolu, traversé de veines blanches franches et graphiques. Le contraste le plus spectaculaire de notre gamme : magnifique en crédence de cuisine, en habillage de vasque ou en sol d'entrée.",
                'image' => '/images/catalogue/marbre-nero-marquina.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Emperador Dark',
                'color' => 'Brun chocolat veiné',
                'color_family' => 'brun',
                'origin' => 'Murcie, Espagne',
                'finish' => 'Polie',
                'applications' => ['sol', 'salle-de-bain', 'escalier'],
                'price_per_m2' => 1450,
                'featured' => false,
                'description' => "Un brun chocolat chaleureux parcouru d'un fin réseau de veines claires. Il réchauffe instantanément une pièce et s'accorde parfaitement avec les menuiseries en noyer et les laitons brossés.",
                'image' => '/images/catalogue/marbre-emperador-dark.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Crema Marfil',
                'color' => 'Crème ivoire',
                'color_family' => 'beige',
                'origin' => 'Alicante, Espagne',
                'finish' => 'Polie',
                'applications' => ['sol', 'escalier', 'salle-de-bain'],
                'price_per_m2' => 1180,
                'featured' => true,
                'description' => "Le grand classique des sols de réception : un beige crème très homogène, à peine nuancé, qui agrandit les volumes et se marie avec tout. Notre référence la plus posée en grandes surfaces de salon.",
                'image' => '/images/catalogue/marbre-crema-marfil.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Bardiglio Gris',
                'color' => 'Gris perle veiné',
                'color_family' => 'gris',
                'origin' => 'Toscane, Italie',
                'finish' => 'Adoucie',
                'applications' => ['salle-de-bain', 'sol', 'cuisine'],
                'price_per_m2' => 1520,
                'featured' => false,
                'description' => "Gris perle nuancé de veines plus sombres, d'une élégance sobre et très contemporaine. Sa finition adoucie, satinée et non glissante, en fait un choix de premier ordre pour les salles d'eau.",
                'image' => '/images/catalogue/marbre-bardiglio-gris.jpg',
            ],

            // ───────────── Marbres marocains ─────────────
            [
                'category' => 'marbre',
                'name' => 'Marbre Beige Zenata',
                'color' => 'Beige sable',
                'color_family' => 'beige',
                'origin' => 'Zenata, Maroc',
                'finish' => 'Adoucie',
                'applications' => ['sol', 'escalier', 'facade'],
                'price_per_m2' => 720,
                'featured' => false,
                'description' => "Marbre marocain extrait à quelques kilomètres de notre dépôt. Un beige sable chaleureux, très régulier, offrant le meilleur rapport qualité-prix de la gamme pour les grandes surfaces de sol et les escaliers.",
                'image' => '/images/catalogue/marbre-beige-zenata.jpg',
            ],
            [
                'category' => 'marbre',
                'name' => 'Marbre Noir Khenifra',
                'color' => 'Noir intense',
                'color_family' => 'noir',
                'origin' => 'Khénifra, Maroc',
                'finish' => 'Polie',
                'applications' => ['sol', 'facade', 'escalier'],
                'price_per_m2' => 890,
                'featured' => false,
                'description' => "Le grand noir marocain, dense et profond, ponctué de fines nervures blanches. Poli miroir, il donne aux sols d'entrée et aux façades une prestance immédiate, à un prix très inférieur aux marbres importés.",
                'image' => '/images/catalogue/marbre-noir-khenifra.jpg',
            ],

            // ───────────── Granits ─────────────
            [
                'category' => 'granit',
                'name' => 'Granit Noir Absolu',
                'color' => 'Noir profond',
                'color_family' => 'noir',
                'origin' => 'Inde du Sud',
                'finish' => 'Polie',
                'applications' => ['cuisine', 'sol', 'facade'],
                'price_per_m2' => 950,
                'featured' => true,
                'description' => "Notre référence la plus demandée en plan de travail. Un noir uniforme sans veine, d'une dureté exceptionnelle : ni rayure, ni tache, ni auréole. Le choix de la tranquillité pour une cuisine très utilisée.",
                'image' => '/images/catalogue/granit-noir-absolu.jpg',
            ],
            [
                'category' => 'granit',
                'name' => 'Granit Perle Blanche',
                'color' => 'Blanc moucheté gris',
                'color_family' => 'blanc',
                'origin' => 'Bejaad, Maroc',
                'finish' => 'Polie',
                'applications' => ['cuisine', 'sol', 'salle-de-bain'],
                'price_per_m2' => 780,
                'featured' => false,
                'description' => "Granit clair au grain fin et régulier, qui agrandit visuellement les cuisines tout en supportant sans broncher l'usage quotidien. Excellent compromis entre la clarté du marbre et la résistance du granit.",
                'image' => '/images/catalogue/granit-perle-blanche.jpg',
            ],
            [
                'category' => 'granit',
                'name' => 'Granit Emerald Pearl',
                'color' => 'Vert nacré',
                'color_family' => 'vert',
                'origin' => 'Norvège',
                'finish' => 'Polie',
                'applications' => ['cuisine', 'facade'],
                'price_per_m2' => 1450,
                'featured' => false,
                'description' => "Des cristaux de feldspath renvoient des reflets vert émeraude qui changent avec la lumière et l'angle de vue. Une pierre de caractère, réservée aux cuisines qui assument une vraie pièce maîtresse.",
                'image' => '/images/catalogue/granit-emerald-pearl.jpg',
            ],
            [
                'category' => 'granit',
                'name' => 'Granit Gris Tarn',
                'color' => 'Gris clair moucheté',
                'color_family' => 'gris',
                'origin' => 'Tarn, France',
                'finish' => 'Adoucie',
                'applications' => ['escalier', 'sol', 'facade'],
                'price_per_m2' => 690,
                'featured' => false,
                'description' => "La référence des escaliers et des circulations intensives. Adouci, il devient antidérapant sans rien perdre de sa finesse, et sa résistance au gel le rend parfait pour les emmarchements extérieurs.",
                'image' => '/images/catalogue/granit-gris-tarn.jpg',
            ],

            // ───────────── Travertins & pierres ─────────────
            [
                'category' => 'travertin-pierre',
                'name' => 'Travertin Beige Classique',
                'color' => 'Beige crème',
                'color_family' => 'beige',
                'origin' => 'Denizli, Turquie',
                'finish' => 'Brossée',
                'applications' => ['sol', 'facade', 'salle-de-bain'],
                'price_per_m2' => 640,
                'featured' => true,
                'description' => "Pierre naturelle à la texture douce et mate, dont le nuancier beige s'accorde naturellement à l'architecture méditerranéenne. Notre choix de prédilection pour les terrasses, les plages de piscine et les façades de villa.",
                'image' => '/images/catalogue/travertin-beige.jpg',
            ],
            [
                'category' => 'travertin-pierre',
                'name' => 'Travertin Noyer',
                'color' => 'Brun noisette rubané',
                'color_family' => 'brun',
                'origin' => 'Denizli, Turquie',
                'finish' => 'Brossée',
                'applications' => ['salle-de-bain', 'sol', 'facade'],
                'price_per_m2' => 830,
                'featured' => false,
                'description' => "Travertin aux rubans bruns et noisette, coupé dans le fil pour révéler ses strates. Sa surface brossée reste agréable et sûre pieds nus, ce qui en fait un excellent choix de salle de bain et de hammam.",
                'image' => '/images/catalogue/travertin-noyer.jpg',
            ],
        ];

        foreach ($products as $attributes) {
            $image = $attributes['image'];
            $family = $attributes['category'];
            unset($attributes['image'], $attributes['category']);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($attributes['name'])],
                $attributes + ['category_id' => $categories[$family]]
            );

            // La photo est réécrite à chaque passage : on repart d'une galerie
            // propre plutôt que d'empiler les doublons.
            $product->images()->delete();
            $product->images()->create(['image_path' => $image, 'is_main' => true]);
        }
    }

    private function seedProjects(): void
    {
        $projects = [
            [
                'title' => 'Villa de luxe à Anfa — Cuisine en Calacatta Gold',
                'category' => 'Villa',
                'location' => 'Anfa Supérieur, Casablanca',
                'year' => 2025,
                'sort_order' => 1,
                'description' => "Îlot central de 3,40 m taillé d'une seule pièce dans un bloc de Calacatta Gold, avec veinage mis en miroir sur la crédence pleine hauteur. Chants massifs de 6 cm, découpe d'évier sous plan et plaque affleurante. Sept semaines entre le choix du bloc et la pose.",
                'cover_image' => '/images/realisations/anfa-calacatta-cuisine.jpg',
                'gallery' => [
                    ['/images/realisations/anfa-calacatta-sdb.jpg', 'Salle de bain principale, plan de toilette assorti'],
                    ['/images/realisations/anfa-calacatta-detail.jpg', "Détail du raccord de veinage à l'angle"],
                ],
            ],
            [
                'title' => 'Appartement de standing Gauthier — Sol en Carrara',
                'category' => 'Appartement',
                'location' => 'Gauthier, Casablanca',
                'year' => 2025,
                'sort_order' => 2,
                'description' => "180 m² de marbre de Carrare posés en grands formats 80 × 80 cm, joints minces et calepinage étudié pour aligner les veines d'une pièce à l'autre. Plinthes assorties débitées dans les mêmes tranches.",
                'cover_image' => '/images/realisations/gauthier-carrara-sol.jpg',
                'gallery' => [
                    ['/images/realisations/gauthier-carrara-sdb.jpg', 'Salle d\'eau en Carrara adouci'],
                ],
            ],
            [
                'title' => 'Riad contemporain à Marrakech — Travertin & patio',
                'category' => 'Résidence',
                'location' => 'Palmeraie, Marrakech',
                'year' => 2024,
                'sort_order' => 3,
                'description' => "Salles de bain, margelles et dallage de patio en travertin beige brossé. Un même matériau du sol intérieur jusqu'à la plage de bassin, pour une continuité totale entre dedans et dehors.",
                'cover_image' => '/images/realisations/marrakech-travertin-sdb.jpg',
                'gallery' => [
                    ['/images/realisations/marrakech-travertin-patio.jpg', 'Patio et bassin en travertin brossé'],
                ],
            ],
            [
                'title' => 'Hôtel boutique Casablanca — Façade en travertin',
                'category' => 'Hôtel',
                'location' => 'Ain Diab, Casablanca',
                'year' => 2024,
                'sort_order' => 4,
                'description' => "420 m² d'habillage de façade en travertin, fixé sur ossature inox avec joints creux réguliers. Traitement hydrofuge adapté à l'air marin de la corniche, et encadrements massifs sur les baies du rez-de-chaussée.",
                'cover_image' => '/images/realisations/hotel-facade-travertin.jpg',
                'gallery' => [
                    ['/images/realisations/hotel-facade-detail.jpg', 'Détail des joints creux et de la trame'],
                    ['/images/realisations/hotel-facade-entree.jpg', "Encadrement massif de l'entrée principale"],
                ],
            ],
            [
                'title' => 'Villa Souissi à Rabat — Escalier & sol en marbre',
                'category' => 'Villa',
                'location' => 'Souissi, Rabat',
                'year' => 2023,
                'sort_order' => 5,
                'description' => "Escalier hélicoïdal de 24 marches en marbre Crema Marfil, chaque marche relevée et taillée sur mesure, nez arrondi et contremarches assorties. Sol de réception et paliers posés dans la même teinte.",
                'cover_image' => '/images/realisations/rabat-escalier-marbre.jpg',
                'gallery' => [
                    ['/images/realisations/rabat-villa-nuit.jpg', 'Façade et emmarchement extérieur de nuit'],
                    ['/images/realisations/rabat-villa-exterieur.jpg', 'Perron et encadrements en marbre beige'],
                ],
            ],
            [
                'title' => 'Résidence Prestige — Piscine & terrasse en pierre',
                'category' => 'Résidence',
                'location' => 'Bouskoura, Casablanca',
                'year' => 2025,
                'sort_order' => 6,
                'description' => "Margelles bord droit, plage de piscine et terrasse en pierre naturelle brossée antidérapante. Découpes courbes réalisées au jet d'eau pour épouser la forme libre du bassin.",
                'cover_image' => '/images/realisations/bouskoura-piscine-pierre.jpg',
                'gallery' => [
                    ['/images/realisations/bouskoura-terrasse-nuit.jpg', 'Terrasse en éclairage nocturne'],
                ],
            ],
        ];

        foreach ($projects as $attributes) {
            $gallery = $attributes['gallery'];
            unset($attributes['gallery']);

            $project = Project::updateOrCreate(['title' => $attributes['title']], $attributes);

            $project->images()->delete();

            foreach ($gallery as $index => [$path, $caption]) {
                $project->images()->create([
                    'image_path' => $path,
                    'caption' => $caption,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
