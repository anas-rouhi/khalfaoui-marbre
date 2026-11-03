<?php

namespace Tests\Feature;

use App\Filament\Resources\ColorFamilyResource\Pages\CreateColorFamily;
use App\Filament\Resources\DevisResource;
use App\Http\Middleware\SetAdminLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Langue du back-office : FR / العربية, RTL, indépendante du site vitrine.
 */
class AdminLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_le_selecteur_est_dans_la_barre_superieure_et_sur_la_connexion(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(route('admin.locale', 'ar'), false)
            ->assertSee('العربية');

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.locale', 'fr'), false)
            ->assertSee(route('admin.locale', 'ar'), false)
            ->assertSee('🇲🇦');
    }

    public function test_le_back_office_est_en_francais_par_defaut(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/teintes')
            ->assertOk()
            ->assertSee('<html', false)
            ->assertSee('lang="fr"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Réglages');
    }

    public function test_basculer_en_arabe_passe_linterface_en_rtl_et_traduit_les_libelles(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/locale/ar')->assertRedirect('/admin');
        $this->assertSame('ar', session(SetAdminLocale::SESSION_KEY));

        $this->get('/admin/teintes')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            // Groupes de navigation et ressources.
            ->assertSee('الإعدادات')
            ->assertSee('الكتالوج')
            ->assertSee('الألوان')
            ->assertSee('طلبات عروض الأسعار')
            // Interface native de Filament (bouton de création).
            ->assertSee('إضافة لون')
            // Police couvrant l'arabe.
            ->assertSee("--font-family: 'Cairo'", false)
            ->assertDontSee('Réglages');
    }

    public function test_le_retour_en_francais_fonctionne(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/locale/ar');
        $this->get('/admin/locale/fr');

        $this->get('/admin/teintes')->assertSee('dir="ltr"', false)->assertSee('Teintes');
    }

    public function test_on_revient_sur_la_page_consultee(): void
    {
        $this->actingAs($this->admin());

        $this->from('/admin/teintes')->get('/admin/locale/ar')->assertRedirect('/admin/teintes');
        // Une page hors back-office ne sert pas de destination.
        $this->from('https://exemple.com/piege')->get('/admin/locale/fr')->assertRedirect('/admin');
    }

    public function test_une_langue_inconnue_est_refusee(): void
    {
        $this->get('/admin/locale/xx')->assertNotFound();
        $this->assertNull(session(SetAdminLocale::SESSION_KEY));
    }

    public function test_la_langue_du_back_office_ne_change_pas_celle_du_site(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/locale/ar');

        // Le site vitrine reste en français…
        $this->get('/')->assertSee('<html lang="fr" dir="ltr"', false);

        // … et l'inverse : passer le site en arabe laisse l'admin en arabe.
        $this->get('/langue/ar');
        $this->get('/')->assertSee('<html lang="ar" dir="rtl"', false);
        $this->get('/admin/teintes')->assertSee('dir="rtl"', false);

        $this->get('/admin/locale/fr');
        $this->get('/admin/teintes')->assertSee('dir="ltr"', false);
        $this->get('/')->assertSee('<html lang="ar" dir="rtl"', false);
    }

    public function test_le_site_en_arabe_ne_passe_pas_ladmin_en_arabe(): void
    {
        $this->get('/langue/ar');

        $this->actingAs($this->admin())
            ->get('/admin/teintes')
            ->assertSee('dir="ltr"', false)
            ->assertSee('Teintes');
    }

    public function test_la_langue_sapplique_aux_requetes_livewire_du_panneau(): void
    {
        // Les filtres, actions et enregistrements passent par /livewire/update :
        // le middleware doit y être réappliqué.
        $this->assertContains(SetAdminLocale::class, Livewire::getPersistentMiddleware());
    }

    public function test_les_erreurs_de_validation_sont_traduites(): void
    {
        $this->actingAs($this->admin());
        app()->setLocale('ar');

        Livewire::test(CreateColorFamily::class)
            ->fillForm(['name' => '', 'key' => '', 'swatch' => '#000000'])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required'])
            ->assertSee('إلزامي');
    }

    /**
     * Garde-fou : tout libellé ajouté au back-office doit recevoir sa
     * traduction, sans quoi il s'afficherait en français dans l'interface
     * arabe.
     */
    public function test_chaque_libelle_du_back_office_a_sa_traduction_arabe(): void
    {
        $translations = json_decode(file_get_contents(lang_path('admin/ar.json')), true, flags: JSON_THROW_ON_ERROR);

        $keys = array_merge($this->adminTranslationKeys(), array_values(DevisResource::STATUSES));
        $missing = array_values(array_diff(array_unique($keys), array_keys($translations)));

        $this->assertSame([], $missing, 'Traductions arabes manquantes dans lang/admin/ar.json');
    }

    /**
     * Clés littérales passées à __() et trans_choice() dans le code du
     * back-office.
     *
     * @return array<int, string>
     */
    private function adminTranslationKeys(): array
    {
        $files = glob(app_path('Providers/Filament/*.php'));

        foreach ([app_path('Filament'), resource_path('views/filament')] as $directory) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                if (str_ends_with($file, '.php')) {
                    $files[] = (string) $file;
                }
            }
        }

        $keys = [];

        foreach ($files as $file) {
            $source = file_get_contents($file);

            // Vues Blade : seul le PHP des {{ }} contient des traductions.
            if (str_ends_with($file, '.blade.php')) {
                preg_match_all('/\{\{(.*?)\}\}/s', $source, $matches);
                $source = '<?php '.implode(";\n", $matches[1]).';';
            }

            $tokens = array_values(array_filter(
                token_get_all($source),
                fn ($token) => ! is_array($token) || ! in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true),
            ));

            foreach ($tokens as $i => $token) {
                if (is_array($token) && $token[0] === T_STRING && in_array($token[1], ['__', 'trans_choice'], true)
                    && ($tokens[$i + 1] ?? null) === '('
                    && is_array($tokens[$i + 2] ?? null) && $tokens[$i + 2][0] === T_CONSTANT_ENCAPSED_STRING) {
                    $key = stripcslashes(substr($tokens[$i + 2][1], 1, -1));

                    // « paquet::clé » : traduit par le paquet (Filament), pas par ce fichier.
                    if (! str_contains($key, '::')) {
                        $keys[] = $key;
                    }
                }
            }
        }

        return $keys;
    }
}
