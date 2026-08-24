<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Marbre', 'slug' => 'marbre', 'name_ar' => 'رخام']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Marbre Carrara Blanc',
            'slug' => 'marbre-carrara-blanc',
            'color' => 'Blanc veiné gris',
            'description' => 'Le marbre italien intemporel.',
        ], $attributes));
    }

    public function test_le_site_est_en_francais_par_defaut(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('lang="fr"', false);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('font-sans', false);
    }

    public function test_la_bascule_vers_larabe_passe_le_document_en_rtl(): void
    {
        $this->get('/langue/ar')->assertRedirect();

        $this->assertSame('ar', session('locale'));

        $response = $this->get('/');
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
        // Cairo n'est chargée qu'en arabe : inutile de la servir en français.
        $response->assertSee('cairo', false);
        $response->assertSee('font-arabic', false);
    }

    public function test_une_langue_inconnue_est_refusee(): void
    {
        $this->get('/langue/de')->assertNotFound();

        $this->assertNull(session('locale'));
    }

    public function test_le_dictionnaire_est_partage_avec_le_front(): void
    {
        $this->get('/langue/ar');

        $this->get('/')->assertInertia(function ($page) {
            $page->where('i18n.locale', 'ar')
                ->where('i18n.direction', 'rtl')
                ->where('i18n.font', 'arabic')
                ->has('i18n.available', 2);

            // Les clés contiennent des points : on lit le tableau directement,
            // sinon Inertia les interprète comme un chemin imbriqué.
            $messages = $page->toArray()['props']['i18n']['messages'];

            $this->assertSame('الرئيسية', $messages['nav.home']);
            $this->assertSame('أرسل طلبي', $messages['quote.submit']);
        });
    }

    public function test_les_deux_dictionnaires_couvrent_les_memes_cles(): void
    {
        $fr = json_decode(file_get_contents(lang_path('fr.json')), true);
        $ar = json_decode(file_get_contents(lang_path('ar.json')), true);

        $this->assertSame([], array_diff(array_keys($fr), array_keys($ar)), 'Clés absentes du dictionnaire arabe');
        $this->assertSame([], array_diff(array_keys($ar), array_keys($fr)), 'Clés arabes sans équivalent français');

        foreach ($fr as $key => $value) {
            $this->assertNotSame('', trim((string) $value), "Traduction française vide : {$key}");
            $this->assertNotSame('', trim((string) $ar[$key]), "Traduction arabe vide : {$key}");
        }
    }

    public function test_une_cle_absente_en_arabe_retombe_sur_le_francais(): void
    {
        $this->get('/langue/ar');

        // Le dictionnaire arabe est fusionné par-dessus le français : même
        // incomplet, aucune clé ne peut manquer côté navigateur.
        $fr = json_decode(file_get_contents(lang_path('fr.json')), true);

        $this->get('/')->assertInertia(function ($page) use ($fr) {
            $messages = $page->toArray()['props']['i18n']['messages'];

            foreach (array_keys($fr) as $key) {
                $this->assertArrayHasKey($key, $messages);
            }
        });
    }

    public function test_le_contenu_traduit_est_servi_en_arabe(): void
    {
        $this->product([
            'name_ar' => 'رخام كرارا الأبيض',
            'description_ar' => 'الرخام الإيطالي الخالد.',
        ]);

        $this->get('/langue/ar');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('products.0.name', 'رخام كرارا الأبيض')
            ->where('products.0.description', 'الرخام الإيطالي الخالد.')
            ->where('products.0.category', 'رخام')
        );
    }

    public function test_le_contenu_non_traduit_retombe_sur_le_francais(): void
    {
        // Nom traduit, description non traduite : la fiche doit rester lisible.
        $this->product(['name_ar' => 'رخام كرارا الأبيض']);

        $this->get('/langue/ar');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('products.0.name', 'رخام كرارا الأبيض')
            ->where('products.0.description', 'Le marbre italien intemporel.')
            ->where('products.0.color', 'Blanc veiné gris')
        );
    }

    public function test_une_traduction_vide_ne_masque_pas_le_texte_francais(): void
    {
        $this->product(['name_ar' => '   ']);

        $this->get('/langue/ar');

        $this->get('/')->assertInertia(
            fn ($page) => $page->where('products.0.name', 'Marbre Carrara Blanc')
        );
    }

    public function test_le_francais_ignore_les_champs_arabes(): void
    {
        $this->product(['name_ar' => 'رخام كرارا الأبيض']);

        $this->get('/')->assertInertia(
            fn ($page) => $page->where('products.0.name', 'Marbre Carrara Blanc')
        );
    }

    public function test_les_realisations_sont_traduites_avec_repli(): void
    {
        Project::create([
            'title' => 'Villa de luxe à Anfa',
            'title_ar' => 'فيلا فاخرة بأنفا',
            'description' => 'Îlot central en Calacatta.',
            'sort_order' => 1,
        ]);

        $this->get('/langue/ar');

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('projects.0.title', 'فيلا فاخرة بأنفا')
            ->where('projects.0.description', 'Îlot central en Calacatta.')
        );
    }

    public function test_le_back_office_reste_en_francais(): void
    {
        $this->get('/langue/ar');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        // Les libellés du panneau sont écrits en français dans les ressources :
        // une bascule ne donnerait qu'une interface à moitié traduite.
        $this->get('/admin/products')
            ->assertOk()
            ->assertSee('Références');
    }
}
