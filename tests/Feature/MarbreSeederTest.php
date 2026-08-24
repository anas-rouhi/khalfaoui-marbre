<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarbreSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_seeder_remplit_le_catalogue_et_le_portfolio(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $this->assertSame(3, Category::count());
        $this->assertSame(14, Product::count());
        $this->assertSame(6, Project::count());
        $this->assertSame(9, ProjectImage::count());

        // Chaque référence a un prix, une origine, une finition et une photo.
        Product::with('images')->get()->each(function (Product $product) {
            $this->assertNotNull($product->price_per_m2, "{$product->name} : prix manquant");
            $this->assertNotEmpty($product->origin, "{$product->name} : origine manquante");
            $this->assertNotEmpty($product->finish, "{$product->name} : finition manquante");
            $this->assertNotEmpty($product->applications, "{$product->name} : usage manquant");
            $this->assertNotEmpty($product->description, "{$product->name} : description manquante");
            $this->assertCount(1, $product->images, "{$product->name} : photo manquante");
        });
    }

    public function test_les_valeurs_de_filtre_restent_celles_du_site(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $familles = array_keys(Product::COLOR_FAMILIES);
        $usages = array_keys(Product::APPLICATIONS);

        Product::all()->each(function (Product $product) use ($familles, $usages) {
            $this->assertContains($product->color_family, $familles, "{$product->name} : teinte hors liste");

            foreach ($product->applications as $application) {
                $this->assertContains($application, $usages, "{$product->name} : usage « {$application} » hors liste");
            }
        });
    }

    public function test_les_photos_referencees_existent_sur_le_disque(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $chemins = Product::with('images')->get()
            ->flatMap(fn (Product $p) => $p->images->pluck('image_path'))
            ->merge(Project::pluck('cover_image'))
            ->merge(ProjectImage::pluck('image_path'))
            ->filter()
            ->unique();

        $this->assertGreaterThanOrEqual(29, $chemins->count());

        foreach ($chemins as $chemin) {
            $this->assertFileExists(public_path(ltrim($chemin, '/')), "photo absente : {$chemin}");
        }
    }

    public function test_le_seeder_est_rejouable_sans_doublon(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        // Une demande de devis reçue entre deux passages ne doit pas disparaître.
        Devis::create(['client_name' => 'Client', 'phone' => '0600000000']);

        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $this->assertSame(14, Product::count());
        $this->assertSame(6, Project::count());
        $this->assertSame(9, ProjectImage::count());
        $this->assertSame(14, \App\Models\ProductImage::count());
        $this->assertSame(1, Devis::count());
    }

    public function test_le_seeder_ne_livre_aucune_photo_avant_travaux(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        // Les vraies paires avant/après viennent des chantiers du client :
        // aucune photo d'illustration ne doit prétendre en être une.
        $this->assertSame(0, Project::whereNotNull('before_image')->count());
    }

    public function test_une_photo_avant_ajoutee_par_ladmin_survit_a_un_reseed(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $project = Project::first();
        $project->update([
            'before_image' => 'projects/mon-chantier-avant.jpg',
            'before_caption' => 'Avant intervention',
        ]);

        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $this->assertSame('projects/mon-chantier-avant.jpg', $project->fresh()->before_image);
        $this->assertSame('Avant intervention', $project->fresh()->before_caption);
    }

    public function test_le_comparateur_ne_recoit_de_donnees_que_pour_une_paire_complete(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        // Sans photo « avant », le composant n'a rien à comparer.
        $this->get('/')->assertInertia(fn ($page) => $page->where('projects.0.beforeImage', null));

        Project::first()->update(['before_image' => '/images/realisations/anfa-calacatta-sdb.jpg']);

        $this->get('/')->assertInertia(
            fn ($page) => $page->where('projects.0.beforeImage', '/images/realisations/anfa-calacatta-sdb.jpg')
        );
    }

    public function test_la_page_daccueil_affiche_tout_le_contenu_seede(): void
    {
        $this->seed(\Database\Seeders\MarbreSeeder::class);

        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('products', 14)
            ->has('projects', 6)
            ->has('categories', 3)
        );
    }
}
