<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Devis;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Support\PdfText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_un_visiteur_arabophone_recoit_un_devis_en_arabe(): void
    {
        $product = $this->product();

        $this->get('/langue/ar');

        $response = $this->post('/devis/pdf', $this->payload([
            'product_id' => $product->id,
            'client_name' => 'سناء العمراني',
        ]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        // Police couvrant l'arabe, embarquée en sous-ensemble.
        $this->assertStringContainsString('DejaVuSans', $response->getContent());
    }

    public function test_un_nom_en_arabe_reste_lisible_dans_le_devis_francais(): void
    {
        $product = $this->product();

        $response = $this->post('/devis/pdf', $this->payload([
            'product_id' => $product->id,
            'client_name' => 'سناء العمراني',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('DejaVuSans', $response->getContent());
    }

    /** Chaque page du PDF est un objet « /Type /Page » (« /Pages » exclu). */
    private function pageCount(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page\b#', $pdf);
    }

    /**
     * HTML du gabarit tel que dompdf l'a reçu : le texte du PDF lui-même est
     * compressé, donc illisible pour une assertion.
     */
    private function renderedPdfHtml(array $overrides = []): string
    {
        $captured = null;
        View::composer('pdf.devis', function ($view) use (&$captured) {
            $captured ??= $view;
        });

        $this->post('/devis/pdf', $this->payload($overrides))->assertOk();

        return $captured->render();
    }

    public static function langues(): array
    {
        return ['français' => ['fr'], 'arabe' => ['ar']];
    }

    #[DataProvider('langues')]
    public function test_le_devis_tient_sur_une_seule_page(string $locale): void
    {
        $product = $this->product();
        $this->get("/langue/{$locale}");

        $response = $this->post('/devis/pdf', $this->payload([
            'product_id' => $product->id,
            // Précisions d'une longueur réaliste.
            'message' => 'Plan de travail en L avec retour de 1,20 m, évier sous-plan, crédence assortie de 60 cm et chants polis. Pose souhaitée avant la fin du mois si possible.',
        ]));

        $response->assertOk();
        $this->assertSame(1, $this->pageCount($response->getContent()));
    }

    public function test_le_devis_rappelle_les_conseils_dentretien(): void
    {
        $html = $this->renderedPdfHtml(['product_id' => $this->product()->id]);

        $this->assertStringContainsString(e(__('pdf.care_title')), $html);

        foreach (__('pdf.care_tips') as $tip) {
            $this->assertStringContainsString(e($tip), $html);
        }
    }

    public function test_les_conseils_dentretien_sont_traduits_en_arabe(): void
    {
        $this->get('/langue/ar');

        $html = $this->renderedPdfHtml(['product_id' => $this->product()->id]);

        // Texte arabe mis en forme pour dompdf (lettres liées, ordre visuel).
        $this->assertStringContainsString(e(PdfText::shape(__('pdf.care_title'))), $html);
        $this->assertNotSame(trans('pdf.care_title', [], 'fr'), __('pdf.care_title'));
        $this->assertCount(count(trans('pdf.care_tips', [], 'fr')), __('pdf.care_tips'));
    }

    public function test_un_devis_francais_sans_arabe_garde_la_police_legere(): void
    {
        $product = $this->product();

        $response = $this->post('/devis/pdf', $this->payload(['product_id' => $product->id]));

        $response->assertOk();
        $this->assertStringNotContainsString('DejaVuSans', $response->getContent());
    }
}
