<?php

namespace Tests\Feature;

use App\Filament\Resources\DevisResource;
use App\Filament\Resources\DevisResource\Pages\ListDevis;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProjectResource;
use App\Models\Category;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_le_panneau_est_protege(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_un_utilisateur_non_admin_est_refuse(): void
    {
        // Un client inscrit via le formulaire public ne doit pas voir /admin.
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get('/admin')->assertForbidden();
        $this->get(ProductResource::getUrl('index'))->assertForbidden();
    }

    public function test_les_trois_ressources_sont_accessibles(): void
    {
        $this->actingAs($this->admin());

        $this->get(ProductResource::getUrl('index'))->assertOk();
        $this->get(ProjectResource::getUrl('index'))->assertOk();
        $this->get(DevisResource::getUrl('index'))->assertOk();
    }

    public function test_une_reference_peut_etre_creee_avec_une_photo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $category = Category::create(['name' => 'Granit', 'slug' => 'granit']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Granit Noir Absolu',
                'slug' => 'granit-noir-absolu',
                'category_id' => $category->id,
                'color' => 'Noir profond',
                'color_family' => 'noir',
                'finish' => 'Poli miroir',
                'origin' => 'Maroc',
                'applications' => ['cuisine', 'sol'],
                'price_per_m2' => 950,
                'featured' => true,
                'images' => [
                    ['image_path' => [UploadedFile::fake()->image('dalle.jpg')], 'is_main' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::firstWhere('slug', 'granit-noir-absolu');

        $this->assertNotNull($product);
        $this->assertSame(['cuisine', 'sol'], $product->applications);
        $this->assertTrue($product->featured);
        $this->assertCount(1, $product->images);

        // Le fichier atterrit bien sur le disque public, dans products/.
        $path = $product->images->first()->image_path;
        $this->assertStringStartsWith('products/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_une_seule_photo_principale_par_reference(): void
    {
        $product = Product::create(['name' => 'Test', 'slug' => 'test']);

        $first = $product->images()->create(['image_path' => 'products/a.jpg', 'is_main' => true]);
        $second = $product->images()->create(['image_path' => 'products/b.jpg', 'is_main' => true]);

        $this->assertFalse($first->fresh()->is_main);
        $this->assertTrue($second->fresh()->is_main);
    }

    public function test_la_photo_televersee_est_servie_au_site_public(): void
    {
        $product = Product::create(['name' => 'Test', 'slug' => 'test-public']);
        $product->images()->create(['image_path' => 'products/a.jpg', 'is_main' => true]);

        // HomeController doit transformer le chemin relatif en URL /storage/...
        $this->get('/')->assertInertia(
            fn ($page) => $page->where('products.0.image', asset('storage/products/a.jpg'))
        );
    }

    public function test_une_realisation_peut_etre_creee(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        Livewire::test(ProjectResource\Pages\CreateProject::class)
            ->fillForm([
                'title' => 'Villa contemporaine',
                'category' => 'Villa',
                'location' => 'Bouskoura, Casablanca',
                'year' => 2025,
                'sort_order' => 1,
                'cover_image' => [UploadedFile::fake()->image('villa.jpg')],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::firstWhere('title', 'Villa contemporaine');

        $this->assertNotNull($project);
        $this->assertStringStartsWith('projects/', $project->cover_image);
        Storage::disk('public')->assertExists($project->cover_image);
    }

    public function test_les_demandes_de_devis_ne_se_creent_pas_depuis_ladmin(): void
    {
        $this->assertFalse(DevisResource::canCreate());
    }

    public function test_le_statut_peut_etre_change_depuis_le_tableau(): void
    {
        $this->actingAs($this->admin());

        $devis = Devis::create([
            'client_name' => 'Youssef Bennani',
            'phone' => '0661219409',
            'status' => 'pending',
        ]);

        Livewire::test(ListDevis::class)
            ->callTableAction('marquer_contacte', $devis);

        $this->assertSame('contacted', $devis->fresh()->status);
    }

    public function test_la_pastille_compte_les_demandes_a_rappeler(): void
    {
        Devis::create(['client_name' => 'A', 'phone' => '0600000001', 'status' => 'pending']);
        Devis::create(['client_name' => 'B', 'phone' => '0600000002', 'status' => 'pending']);
        Devis::create(['client_name' => 'C', 'phone' => '0600000003', 'status' => 'completed']);

        $this->assertSame('2', DevisResource::getNavigationBadge());
    }

    #[DataProvider('numeros')]
    public function test_les_numeros_sont_normalises_pour_whatsapp(string $saisi, string $attendu): void
    {
        $this->assertSame($attendu, DevisResource::normalisePhone($saisi));
    }

    public static function numeros(): array
    {
        return [
            'national' => ['0661219409', '212661219409'],
            'avec espaces' => ['06 61 21 94 09', '212661219409'],
            'international +' => ['+212661219409', '212661219409'],
            'international 00' => ['00212661219409', '212661219409'],
            'tirets' => ['0661-219409', '212661219409'],
        ];
    }

    public function test_le_lien_whatsapp_contient_le_message_de_rappel(): void
    {
        $category = Category::create(['name' => 'Granit', 'slug' => 'granit']);
        $product = Product::create(['name' => 'Granit Noir Absolu', 'slug' => 'gna', 'category_id' => $category->id]);

        $devis = Devis::create([
            'client_name' => 'Youssef',
            'phone' => '0661219409',
            'product_id' => $product->id,
            'surface_m2' => 14.5,
            'status' => 'pending',
        ]);

        $url = DevisResource::whatsappUrl($devis->fresh());

        $this->assertStringStartsWith('https://wa.me/212661219409?text=', $url);

        $message = rawurldecode(str($url)->after('text=')->toString());
        $this->assertStringContainsString('Bonjour Youssef', $message);
        $this->assertStringContainsString('Granit Noir Absolu', $message);
        $this->assertStringContainsString('14.5 m²', $message);
    }
}
