<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\ColorFamilyResource;
use App\Filament\Resources\ColorFamilyResource\Pages\CreateColorFamily;
use App\Filament\Resources\ColorFamilyResource\Pages\EditColorFamily;
use App\Filament\Resources\ColorFamilyResource\Pages\ListColorFamilies;
use App\Filament\Resources\InstallationRateResource\Pages\CreateInstallationRate;
use App\Filament\Resources\InstallationRateResource\Pages\ListInstallationRates;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\ColorFamily;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\InstallationRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Teintes et usages du catalogue : administrés en base, ils alimentent
 * directement les fiches, les filtres du site et l'estimateur.
 */
class CatalogueFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InstallationRateSeeder::class);
    }

    private function admin(): User
    {
        Storage::fake('public');

        return User::factory()->create(['is_admin' => true]);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'marbre'], ['name' => 'Marbre']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Marbre Carrara',
            'slug' => 'carrara-'.uniqid(),
            'color_family' => 'blanc',
            'applications' => ['sol'],
        ], $attributes));
    }

    public function test_les_teintes_dorigine_sont_en_base(): void
    {
        $this->assertSame(['noir', 'blanc', 'gris', 'beige', 'brun', 'vert'], ColorFamily::query()->ordered()->pluck('key')->all());
    }

    public function test_la_page_recoit_les_filtres_de_la_base(): void
    {
        $this->product();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('colorFamilies', 6)
            ->where('colorFamilies.0', ['key' => 'noir', 'label' => 'Noir', 'swatch' => '#141519'])
            ->has('applications', 5)
            ->where('applications.0', ['key' => 'cuisine', 'label' => 'Plan de travail / Cuisine'])
        );
    }

    public function test_les_filtres_sont_traduits_en_arabe(): void
    {
        $this->product();

        $this->get('/?lang=ar')->assertInertia(fn ($page) => $page
            ->where('colorFamilies.0.label', 'أسود')
            ->where('applications.0.label', 'سطح مطبخ')
            ->where('installationRates.0.label', 'سطح مطبخ')
        );
    }

    public function test_une_teinte_creee_dans_ladmin_devient_un_filtre(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateColorFamily::class)
            ->fillForm(['name' => 'Rose', 'name_ar' => 'وردي', 'swatch' => '#e8b4b8'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('color_families', ['key' => 'rose', 'name' => 'Rose']);

        // Sélectionnable aussitôt sur une fiche marbre…
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Marbre Rosa Portogallo',
                'slug' => 'rosa-portogallo',
                'color_family' => 'rose',
                'applications' => ['sol'],
                'images' => [['image_path' => [UploadedFile::fake()->image('rosa.jpg')], 'is_main' => true]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // … et transmise au site avec sa pastille.
        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('products.0.colorFamily', 'rose')
            ->where('colorFamilies.6', ['key' => 'rose', 'label' => 'Rose', 'swatch' => '#e8b4b8'])
        );
    }

    public function test_un_usage_cree_dans_ladmin_alimente_fiches_filtre_et_devis(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateInstallationRate::class)
            ->fillForm(['label' => 'Terrasse', 'label_ar' => 'شرفة', 'rate_per_m2' => 140, 'sort_order' => 6, 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('installation_rates', ['application' => 'terrasse', 'label' => 'Terrasse']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Travertin Beige',
                'slug' => 'travertin-beige',
                'color_family' => 'beige',
                'applications' => ['terrasse', 'sol'],
                'images' => [['image_path' => [UploadedFile::fake()->image('travertin.jpg')], 'is_main' => true]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('applications.5', ['key' => 'terrasse', 'label' => 'Terrasse'])
            ->where('products.0.applications', ['terrasse', 'sol'])
        );

        // L'estimateur l'accepte et applique son tarif.
        auth()->logout();
        $this->post('/devis', [
            'client_name' => 'Client',
            'phone' => '0661219409',
            'application' => 'terrasse',
        ])->assertSessionHasNoErrors();
    }

    public function test_un_usage_desactive_reste_un_filtre_mais_quitte_lestimateur(): void
    {
        $this->product(['applications' => ['facade']]);
        InstallationRate::firstWhere('application', 'facade')->update(['is_active' => false]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('applications', 5)
            ->has('installationRates', 4)
        );
    }

    public function test_lidentifiant_dune_teinte_est_verrouille_apres_creation(): void
    {
        $this->actingAs($this->admin());

        $this->get(ColorFamilyResource::getUrl('edit', ['record' => ColorFamily::firstWhere('key', 'noir')]))
            ->assertOk();

        Livewire::test(EditColorFamily::class, [
            'record' => ColorFamily::firstWhere('key', 'noir')->getKey(),
        ])
            ->assertFormFieldIsDisabled('key');
    }

    public function test_une_teinte_ou_un_usage_utilise_ne_peut_pas_etre_supprime(): void
    {
        $this->actingAs($this->admin());
        $this->product(['color_family' => 'noir', 'applications' => ['cuisine']]);

        Livewire::test(ListColorFamilies::class)
            ->assertTableActionHidden('delete', ColorFamily::firstWhere('key', 'noir'))
            ->assertTableActionVisible('delete', ColorFamily::firstWhere('key', 'vert'));

        Livewire::test(ListInstallationRates::class)
            ->assertTableActionHidden('delete', InstallationRate::firstWhere('application', 'cuisine'))
            ->assertTableActionVisible('delete', InstallationRate::firstWhere('application', 'escalier'));
    }

    public function test_les_pages_reglages_sont_accessibles(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/teintes')->assertOk();
        $this->get('/admin/teintes/create')->assertOk();
        $this->get('/admin/tarifs-pose')->assertOk()->assertSee('Usages');
        $this->get('/admin/familles')->assertOk()->assertSee('Familles de matière');
        $this->get('/admin/familles/create')->assertOk();
    }

    // ── Familles de matière ──

    public function test_la_page_recoit_les_familles_dans_lordre_du_back_office(): void
    {
        $granit = Category::create(['name' => 'Granit', 'name_ar' => 'غرانيت', 'slug' => 'granit', 'sort_order' => 2]);
        $marbre = Category::create(['name' => 'Marbre', 'name_ar' => 'رخام', 'slug' => 'marbre', 'sort_order' => 1]);
        $this->product(['category_id' => $granit->id]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('categories', [
                ['key' => 'marbre', 'label' => 'Marbre'],
                ['key' => 'granit', 'label' => 'Granit'],
            ])
            ->where('products.0.categoryKey', 'granit')
        );

        $this->get('/?lang=ar')->assertInertia(fn ($page) => $page
            ->where('categories.0.label', 'رخام')
            ->where('categories.1.label', 'غرانيت')
        );
    }

    public function test_une_famille_creee_dans_ladmin_devient_un_filtre(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateCategory::class)
            ->fillForm(['name' => 'Quartz', 'name_ar' => 'كوارتز'])
            ->call('create')
            ->assertHasNoFormErrors();

        $quartz = Category::firstWhere('slug', 'quartz');
        $this->assertNotNull($quartz, 'Le slug doit être généré depuis le nom.');

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Quartz Blanc Étoilé',
                'slug' => 'quartz-blanc-etoile',
                'category_id' => $quartz->id,
                'color_family' => 'blanc',
                'applications' => ['cuisine'],
                'images' => [['image_path' => [UploadedFile::fake()->image('quartz.jpg')], 'is_main' => true]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('categories', [['key' => 'quartz', 'label' => 'Quartz']])
            ->where('products.0.categoryKey', 'quartz')
        );
    }

    public function test_une_famille_peut_etre_creee_depuis_la_fiche_marbre(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateProduct::class)
            ->callFormComponentAction('category_id', 'createOption', [
                'name' => 'Pierre naturelle',
                'slug' => 'pierre-naturelle',
                'name_ar' => 'حجر طبيعي',
            ])
            ->assertHasNoFormComponentActionErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'pierre-naturelle', 'name_ar' => 'حجر طبيعي']);
    }

    public function test_une_famille_utilisee_ne_peut_pas_etre_supprimee_et_son_slug_est_verrouille(): void
    {
        $this->actingAs($this->admin());

        $marbre = Category::create(['name' => 'Marbre', 'slug' => 'marbre']);
        $quartz = Category::create(['name' => 'Quartz', 'slug' => 'quartz']);
        $this->product(['category_id' => $marbre->id]);

        Livewire::test(ListCategories::class)
            ->assertTableActionHidden('delete', $marbre)
            ->assertTableActionVisible('delete', $quartz);

        Livewire::test(EditCategory::class, ['record' => $marbre->getKey()])
            ->assertFormFieldIsDisabled('slug');
    }
}
