<?php

namespace Tests\Feature;

use App\Filament\Resources\DevisResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\DevisActivityChart;
use App\Filament\Widgets\LatestDevis;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

use function Livewire\invade;

/**
 * Tableau de bord du back-office : bannière, indicateurs, graphique et
 * dernières demandes.
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['is_admin' => true, 'name' => 'Anas']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function devis(array $attributes = []): Devis
    {
        return Devis::forceCreate($attributes + [
            'client_name' => 'Client',
            'phone' => '0661219409',
            'status' => 'pending',
        ]);
    }

    public function test_la_banniere_accueille_et_propose_les_raccourcis(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Bonjour, Anas')
            ->assertSee("Bienvenue sur l'espace d'administration de")
            ->assertSee('Khalfaoui Marbre')
            ->assertSee(ProductResource::getUrl('create'), false)
            ->assertSee(ProjectResource::getUrl('create'), false)
            ->assertSee('Voir le site vitrine')
            // Les autres widgets sont bien montés sur la page.
            ->assertSeeLivewire(DashboardStats::class)
            ->assertSeeLivewire(DevisActivityChart::class)
            ->assertSeeLivewire(LatestDevis::class);
    }

    public function test_le_tableau_de_bord_passe_en_arabe(): void
    {
        $this->get('/admin/locale/ar');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('مرحبًا، Anas')
            ->assertSee('مرحبًا بكم في فضاء إدارة')
            ->assertSee('خلفاوي رخام')
            ->assertSee('إضافة رخام')
            ->assertDontSee('Ajouter un marbre');

        app()->setLocale('ar');

        Livewire::test(DashboardStats::class)
            ->assertSee('طلبات في الانتظار')
            ->assertSee('المادة الأكثر طلبًا')
            ->assertSee('الإنجازات المنشورة');
    }

    public function test_les_indicateurs_refletent_les_demandes_et_les_chantiers(): void
    {
        $marbre = Product::create(['name' => 'Marbre Calacatta', 'slug' => 'calacatta']);
        $granit = Product::create(['name' => 'Granit Noir', 'slug' => 'granit-noir']);

        $this->devis(['product_id' => $marbre->id, 'surface_m2' => 12.5]);
        $this->devis(['product_id' => $marbre->id, 'surface_m2' => 1000]);
        $this->devis(['product_id' => $granit->id, 'status' => 'completed']);

        Project::create(['title' => 'Villa', 'is_published' => true]);
        Project::create(['title' => 'Hôtel', 'is_published' => true]);
        Project::create(['title' => 'Riad', 'is_published' => false]);

        Livewire::test(DashboardStats::class)
            ->assertSeeInOrder(['Devis en attente', '2', 'À rappeler au plus vite'])
            // Espace fine insécable : le nombre reste d'un bloc en arabe.
            ->assertSee("1\u{202F}012,5 m²")
            ->assertSeeInOrder(['Matériau le plus demandé', 'Marbre Calacatta', '2 demandes sur 30 jours'])
            ->assertSeeInOrder(['Réalisations en ligne', '2', '1 brouillon hors ligne']);
    }

    public function test_sans_demande_en_attente_la_carte_passe_au_vert(): void
    {
        $this->devis(['status' => 'completed']);

        Livewire::test(DashboardStats::class)
            ->assertSee('Toutes les demandes sont traitées')
            ->assertSee('Aucune demande avec matériau');
    }

    public function test_le_materiau_tendance_se_rabat_sur_lhistorique(): void
    {
        $product = Product::create(['name' => 'Travertin', 'slug' => 'travertin']);
        $this->devis(['product_id' => $product->id])->forceFill(['created_at' => now()->subDays(60)])->save();

        Livewire::test(DashboardStats::class)
            ->assertSee('Travertin')
            ->assertSee('1 demande au total');
    }

    public function test_le_materiau_tendance_est_traduit(): void
    {
        $product = Product::create(['name' => 'Granit Noir', 'name_ar' => 'غرانيت أسود', 'slug' => 'gn']);
        $this->devis(['product_id' => $product->id]);
        $this->devis(['product_id' => $product->id]);

        app()->setLocale('ar');

        Livewire::test(DashboardStats::class)
            ->assertSee('غرانيت أسود')
            ->assertSee('طلبان خلال 30 يومًا');
    }

    public function test_le_graphique_compte_les_demandes_par_jour(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');

        $this->devis()->forceFill(['created_at' => '2026-11-03 08:00:00'])->save();
        $this->devis()->forceFill(['created_at' => '2026-11-03 09:00:00'])->save();
        $this->devis()->forceFill(['created_at' => '2026-11-01 12:00:00'])->save();
        // Hors fenêtre de 30 jours.
        $this->devis()->forceFill(['created_at' => '2026-09-15 12:00:00'])->save();

        $series = invade(Livewire::test(DevisActivityChart::class)->instance())->series();

        $this->assertCount(30, $series['counts']);
        $this->assertSame(2, $series['counts'][29]);
        $this->assertSame(0, $series['counts'][28]);
        $this->assertSame(1, $series['counts'][27]);
        $this->assertSame(3, array_sum($series['counts']));
    }

    public function test_le_graphique_compte_les_demandes_par_mois(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');

        $this->devis()->forceFill(['created_at' => '2026-11-01 08:00:00'])->save();
        $this->devis()->forceFill(['created_at' => '2026-09-15 12:00:00'])->save();
        $this->devis()->forceFill(['created_at' => '2026-09-20 12:00:00'])->save();
        // Plus de 12 mois : ignoré.
        $this->devis()->forceFill(['created_at' => '2025-10-31 12:00:00'])->save();

        $component = Livewire::test(DevisActivityChart::class)
            ->set('filter', '12m')
            ->assertSee('3 demandes sur 12 mois');

        $series = invade($component->instance())->series();

        $this->assertCount(12, $series['counts']);
        $this->assertSame(1, $series['counts'][11]);
        $this->assertSame(2, $series['counts'][9]);
        $this->assertSame(3, array_sum($series['counts']));
    }

    public function test_les_dernieres_demandes_se_limitent_aux_cinq_plus_recentes(): void
    {
        $old = $this->devis(['client_name' => 'Ancien']);
        $old->forceFill(['created_at' => now()->subDays(10)])->save();

        foreach (range(1, 5) as $i) {
            $this->devis(['client_name' => "Récent {$i}"])->forceFill(['created_at' => now()->subHours($i)])->save();
        }

        // Le total compté par Filament ignore `limit()` : on vérifie les lignes affichées.
        $component = Livewire::test(LatestDevis::class)
            ->assertCanSeeTableRecords(Devis::where('client_name', '!=', 'Ancien')->get())
            ->assertCanNotSeeTableRecords([$old])
            ->assertDontSee('Ancien');

        $this->assertCount(LatestDevis::LIMIT, $component->instance()->getTableRecords());
    }

    public function test_les_dernieres_demandes_proposent_whatsapp_et_appel(): void
    {
        $devis = $this->devis(['client_name' => 'Youssef', 'phone' => '06 61 21 94 09']);

        Livewire::test(LatestDevis::class)
            ->assertTableActionHasUrl('whatsapp', DevisResource::whatsappUrl($devis), $devis)
            ->assertTableActionHasUrl('appeler', 'tel:+212661219409', $devis)
            ->assertSee('Youssef');
    }
}
