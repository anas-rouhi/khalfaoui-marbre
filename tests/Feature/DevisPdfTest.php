<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Devis;
use App\Models\InstallationRate;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevisPdfTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'Marbre', 'slug' => 'marbre']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Marbre Carrara Blanc',
            'slug' => 'marbre-carrara-blanc',
            'origin' => 'Carrare, Italie',
            'finish' => 'Polie',
            'applications' => ['cuisine'],
            'price_per_m2' => 1650,
        ]);

        $product->images()->create([
            'image_path' => '/images/catalogue/marbre-carrara.jpg',
            'is_main' => true,
        ]);

        InstallationRate::create([
            'application' => 'cuisine',
            'label' => 'Plan de travail / Cuisine',
            'rate_per_m2' => 220,
        ]);

        return $product;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Sanaa El Amrani',
            'phone' => '0612345678',
            'email' => 'sanaa@example.com',
            'location' => 'Souissi, Rabat',
            'application' => 'cuisine',
            'surface_m2' => 18,
        ], $overrides);
    }

    public function test_le_devis_pdf_est_telechargeable(): void
    {
        $product = $this->product();

        $response = $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $devis = Devis::first();
        $this->assertNotNull($devis);
        $response->assertDownload($devis->reference.'.pdf');

        // Un vrai PDF commence par cette signature.
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_le_pdf_enregistre_la_demande_avec_le_total_complet(): void
    {
        $product = $this->product();

        $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]))->assertOk();

        // 18 × 1650 (fourniture) + 18 × 220 (pose) = 33 660
        $this->assertSame('33660.00', Devis::first()->estimated_total);
        $this->assertSame('pending', Devis::first()->status);
    }

    public function test_la_reference_suit_le_format_attendu(): void
    {
        $product = $this->product();

        $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]))->assertOk();

        $this->assertMatchesRegularExpression('/^DEV-\d{4}-\d{4}$/', Devis::first()->reference);
    }

    public function test_les_erreurs_sont_renvoyees_en_json_et_non_en_redirection(): void
    {
        // Sans ce comportement, le navigateur suivrait la redirection et
        // enregistrerait la page d'accueil sous le nom « devis.pdf ».
        $response = $this->postJson('/devis/pdf', $this->payload([
            'client_name' => '',
            'phone' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_name', 'phone']);

        $this->assertDatabaseCount('devis', 0);
    }

    public function test_meme_avec_un_accept_pdf_les_erreurs_restent_en_json(): void
    {
        $response = $this->post('/devis/pdf', ['client_name' => '', 'phone' => ''], [
            'Accept' => 'application/pdf',
        ]);

        $response->assertStatus(422);
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_deux_telechargements_dans_la_meme_session_ne_font_quune_fiche(): void
    {
        $product = $this->product();

        $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]))->assertOk();
        $this->post('/devis/pdf', $this->payload(['product_id' => $product->id, 'surface_m2' => 25]))->assertOk();

        $this->assertDatabaseCount('devis', 1);
        // La fiche reflète la dernière version demandée.
        $this->assertSame('25.00', Devis::first()->surface_m2);
    }

    public function test_telecharger_puis_envoyer_ne_cree_pas_de_doublon(): void
    {
        $product = $this->product();

        $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]))->assertOk();
        $this->post('/devis', $this->payload(['product_id' => $product->id]))->assertRedirect();

        $this->assertDatabaseCount('devis', 1);
    }

    public function test_le_pdf_fonctionne_sans_materiau_selectionne(): void
    {
        $response = $this->post('/devis/pdf', $this->payload(['product_id' => null]));

        $response->assertOk();
        $this->assertNull(Devis::first()->estimated_total);
    }
}
