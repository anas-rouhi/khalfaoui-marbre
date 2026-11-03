<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\MediaPath;
use App\Support\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_entetes_de_securite_sont_poses(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            // HSTS seulement en production.
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_est_envoye_en_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    #[DataProvider('pagesBreeze')]
    public function test_les_pages_de_compte_publiques_nexistent_plus(string $url): void
    {
        $this->get($url)->assertNotFound();
    }

    public static function pagesBreeze(): array
    {
        return [
            'inscription' => ['/register'],
            'connexion' => ['/login'],
            'tableau de bord' => ['/dashboard'],
            'profil' => ['/profile'],
            'mot de passe oublié' => ['/forgot-password'],
        ];
    }

    public function test_la_page_expose_les_metadonnees_de_partage_et_hreflang(): void
    {
        $home = rtrim(route('home'), '/').'/';

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$home.'">', false)
            ->assertSee('hreflang="fr" href="'.$home.'"', false)
            ->assertSee('hreflang="ar" href="'.$home.'?lang=ar"', false)
            ->assertSee('hreflang="x-default"', false)
            ->assertSee('property="og:image" content="'.asset('images/hero/hero-cuisine-granit.jpg').'"', false)
            ->assertSee('property="og:url" content="'.$home.'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('rel="preload" as="image" type="image/webp"', false);
    }

    public function test_les_donnees_structurees_decrivent_lentreprise(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $match);
        $schema = json_decode($match[1] ?? '', true);

        $this->assertSame('HomeGoodsStore', $schema['@type']);
        $this->assertSame('KHALFAOUI MARBRE', $schema['name']);
        $this->assertSame(config('company.phone_e164'), $schema['telephone']);
        $this->assertSame('Casablanca', $schema['address']['addressLocality']);
        $this->assertSame(config('company.geo.lat'), $schema['geo']['latitude']);
        $this->assertCount(2, $schema['openingHoursSpecification']);

        // Logo matriciel et réellement livré : Google refuse le SVG.
        $this->assertSame(asset(Seo::SCHEMA_LOGO), $schema['logo']);
        $this->assertFileExists(public_path(Seo::SCHEMA_LOGO));
        $this->assertStringStartsWith('https://www.google.com/maps/', $schema['hasMap']);
    }

    public function test_la_meta_description_tient_dans_lextrait_google(): void
    {
        foreach (array_keys(config('localization.supported')) as $locale) {
            $length = mb_strlen(trans('seo.description', [], $locale));

            $this->assertLessThanOrEqual(155, $length, "Description « {$locale} » tronquée par Google.");
            $this->assertGreaterThanOrEqual(110, $length, "Description « {$locale} » trop courte.");
        }
    }

    public function test_lurl_arabe_affiche_la_version_arabe(): void
    {
        $this->get('/?lang=ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl"', false)
            ->assertSee('<link rel="canonical" href="'.rtrim(route('home'), '/').'/?lang=ar">', false)
            ->assertSee('content="ar_MA"', false);
    }

    public function test_une_langue_inconnue_en_parametre_est_ignoree(): void
    {
        $this->get('/?lang=xx')->assertSee('<html lang="fr"', false);
    }

    public function test_le_sitemap_liste_les_pages_et_les_photos(): void
    {
        $category = Category::create(['name' => 'Marbre', 'slug' => 'marbre']);
        $product = Product::create(['name' => 'Marbre Carrara', 'slug' => 'carrara', 'category_id' => $category->id]);
        $product->images()->create(['image_path' => '/images/catalogue/marbre-carrara.jpg', 'is_main' => true]);

        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Le sitemap doit être un XML valide.');

        $response
            ->assertSee('<loc>'.rtrim(route('home'), '/').'/</loc>', false)
            ->assertSee('<loc>'.rtrim(route('home'), '/').'/?lang=ar</loc>', false)
            ->assertSee('<image:loc>'.url('/images/catalogue/marbre-carrara.webp').'</image:loc>', false)
            ->assertSee('<image:title>Marbre Carrara</image:title>', false);
    }

    public function test_robots_txt_bloque_ladmin_et_annonce_le_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_ziggy_nexpose_que_les_routes_publiques(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('"devis.store"', $html);
        $this->assertStringNotContainsString('filament.admin', $html);
    }

    public function test_une_copie_webp_est_servie_quand_elle_existe(): void
    {
        $this->assertSame('/images/catalogue/marbre-carrara.webp', MediaPath::url('/images/catalogue/marbre-carrara.jpg'));
        // Pas de copie : l'original est servi.
        $this->assertSame('/images/brand/khalfaoui-marbre-logo.png', MediaPath::url('/images/brand/khalfaoui-marbre-logo.png'));
    }
}
