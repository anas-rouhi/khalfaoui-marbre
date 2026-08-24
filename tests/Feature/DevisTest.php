<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Devis;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Granit', 'slug' => 'granit']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Granit Noir Absolu',
            'slug' => 'granit-noir-absolu',
            'color' => 'Noir profond',
            'color_family' => 'noir',
            'finish' => 'Poli miroir',
            'applications' => ['cuisine', 'sol'],
            'price_per_m2' => 950,
            'featured' => true,
        ], $attributes));
    }

    public function test_la_page_daccueil_expose_le_catalogue(): void
    {
        $product = $this->product();
        $product->images()->create(['image_path' => '/images/catalogue/granit-noir-absolu.jpg', 'is_main' => true]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('products', 1)
                ->where('products.0.name', 'Granit Noir Absolu')
                // Le round-trip JSON ramène 950.0 à un entier.
                ->where('products.0.pricePerM2', 950)
                ->where('products.0.image', '/images/catalogue/granit-noir-absolu.jpg')
                ->where('company.phone_display', config('company.phone_display'))
                ->where('company.email', config('company.email'))
            );
    }

    public function test_la_galerie_dune_realisation_est_exposee(): void
    {
        $project = \App\Models\Project::create([
            'title' => 'Villa de luxe à Anfa',
            'category' => 'Villa',
            'location' => 'Anfa, Casablanca',
            'year' => 2025,
            'cover_image' => '/images/realisations/anfa-calacatta-cuisine.jpg',
        ]);

        $project->images()->create([
            'image_path' => '/images/realisations/anfa-calacatta-sdb.jpg',
            'caption' => 'Salle de bain principale',
            'sort_order' => 1,
        ]);
        $project->images()->create([
            'image_path' => '/images/realisations/anfa-calacatta-detail.jpg',
            'caption' => 'Détail du raccord',
            'sort_order' => 0,
        ]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('projects.0.gallery', 2)
            // Le tri se fait sur sort_order, pas sur l'ordre d'insertion.
            ->where('projects.0.gallery.0.caption', 'Détail du raccord')
            ->where('projects.0.gallery.1.caption', 'Salle de bain principale')
            ->where('projects.0.gallery.0.image', '/images/realisations/anfa-calacatta-detail.jpg')
        );
    }

    public function test_une_demande_de_devis_est_enregistree(): void
    {
        $product = $this->product();

        $this->post('/devis', [
            'client_name' => 'Youssef Bennani',
            'phone' => '0661219409',
            'email' => 'youssef@example.com',
            'location' => 'Bouskoura',
            'product_id' => $product->id,
            'application' => 'cuisine',
            'surface_m2' => 12,
            'message' => 'Plan de travail en U.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('devis', [
            'client_name' => 'Youssef Bennani',
            'phone' => '0661219409',
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }

    public function test_lestimation_est_recalculee_cote_serveur(): void
    {
        $product = $this->product(['price_per_m2' => 950]);

        \App\Models\InstallationRate::create([
            'application' => 'cuisine',
            'label' => 'Plan de travail / Cuisine',
            'rate_per_m2' => 220,
        ]);

        // Le total soumis par le client est volontairement fantaisiste.
        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => $product->id,
            'application' => 'cuisine',
            'surface_m2' => 10,
            'estimated_total' => 1,
        ])->assertRedirect();

        // Fourniture ET pose, comme sur l'écran du visiteur :
        // 10 × 950 + 10 × 220 = 11 700.
        $this->assertSame('11700.00', Devis::first()->estimated_total);
    }

    public function test_le_nom_et_le_telephone_sont_obligatoires(): void
    {
        $this->post('/devis', ['client_name' => '', 'phone' => ''])
            ->assertSessionHasErrors(['client_name', 'phone']);

        $this->assertDatabaseCount('devis', 0);
    }

    public function test_un_produit_inexistant_est_rejete(): void
    {
        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => 99999,
        ])->assertSessionHasErrors('product_id');

        $this->assertDatabaseCount('devis', 0);
    }
}
