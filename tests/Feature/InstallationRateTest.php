<?php

namespace Tests\Feature;

use App\Filament\Resources\InstallationRateResource;
use App\Filament\Resources\InstallationRateResource\Pages\ListInstallationRates;
use App\Models\Category;
use App\Models\Devis;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstallationRateTest extends TestCase
{
    use RefreshDatabase;

    private function product(float $pricePerM2 = 1000): Product
    {
        $category = Category::create(['name' => 'Granit', 'slug' => 'granit']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Granit Test',
            'slug' => 'granit-test',
            'applications' => ['cuisine', 'sol'],
            'price_per_m2' => $pricePerM2,
        ]);
    }

    private function rate(string $application, float $rate): InstallationRate
    {
        return InstallationRate::create([
            'application' => $application,
            'label' => ucfirst($application),
            'rate_per_m2' => $rate,
        ]);
    }

    public function test_le_seeder_pose_les_tarifs_de_reference(): void
    {
        $this->seed(\Database\Seeders\InstallationRateSeeder::class);

        $this->assertSame(5, InstallationRate::count());
        $this->assertSame('220.00', InstallationRate::firstWhere('application', 'cuisine')->rate_per_m2);
    }

    public function test_le_seeder_necrase_pas_un_tarif_ajuste(): void
    {
        $this->seed(\Database\Seeders\InstallationRateSeeder::class);

        InstallationRate::firstWhere('application', 'cuisine')->update(['rate_per_m2' => 999]);

        $this->seed(\Database\Seeders\InstallationRateSeeder::class);

        $this->assertSame(5, InstallationRate::count());
        $this->assertSame('999.00', InstallationRate::firstWhere('application', 'cuisine')->rate_per_m2);
    }

    public function test_les_tarifs_actifs_sont_transmis_a_la_page(): void
    {
        $this->rate('cuisine', 220)->update(['sort_order' => 1]);
        $this->rate('sol', 120)->update(['sort_order' => 2]);
        $this->rate('facade', 260)->update(['is_active' => false]);

        $this->get('/')->assertInertia(fn ($page) => $page
            // La façade est désactivée : elle ne doit pas être proposée.
            ->has('installationRates', 2)
            ->where('installationRates.0.key', 'cuisine')
            ->where('installationRates.0.label', 'Cuisine')
            ->where('installationRates.0.ratePerM2', 220)
            ->where('installationRates.1.key', 'sol')
        );
    }

    public function test_lestimation_enregistree_inclut_la_pose(): void
    {
        $product = $this->product(1000);
        $this->rate('cuisine', 220);

        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => $product->id,
            'application' => 'cuisine',
            'surface_m2' => 10,
        ])->assertRedirect();

        // 10 × 1000 (fourniture) + 10 × 220 (pose) = 12 200
        $this->assertSame('12200.00', Devis::first()->estimated_total);
    }

    public function test_changer_le_tarif_change_le_montant_enregistre(): void
    {
        $product = $this->product(1000);
        $rate = $this->rate('cuisine', 220);

        // Le gérant double son tarif de pose depuis le back-office.
        $rate->update(['rate_per_m2' => 440]);

        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => $product->id,
            'application' => 'cuisine',
            'surface_m2' => 10,
        ])->assertRedirect();

        $this->assertSame('14400.00', Devis::first()->estimated_total);
    }

    public function test_une_application_sans_tarif_utilise_la_valeur_de_repli(): void
    {
        $product = $this->product(1000);

        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => $product->id,
            'application' => 'escalier',
            'surface_m2' => 10,
        ])->assertRedirect();

        // 10 × 1000 + 10 × 150 (repli) = 11 500
        $this->assertSame('11500.00', Devis::first()->estimated_total);
    }

    public function test_un_tarif_desactive_reste_applique_aux_demandes_recues(): void
    {
        $product = $this->product(1000);
        $this->rate('cuisine', 220)->update(['is_active' => false]);

        $this->post('/devis', [
            'client_name' => 'Test',
            'phone' => '0600000000',
            'product_id' => $product->id,
            'application' => 'cuisine',
            'surface_m2' => 10,
        ])->assertRedirect();

        // Le tarif reste connu même s'il n'est plus proposé : 12 200, pas 11 500.
        $this->assertSame('12200.00', Devis::first()->estimated_total);
    }

    public function test_lecran_dadministration_est_accessible(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->seed(\Database\Seeders\InstallationRateSeeder::class);

        $this->get(InstallationRateResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Plan de travail / Cuisine');
    }

    public function test_le_tarif_se_modifie_directement_dans_le_tableau(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $rate = $this->rate('cuisine', 220);

        // `updateTableColumnState` est le point d'entrée qu'appelle la colonne
        // éditable dans le navigateur : on teste le vrai chemin, pas un update
        // manuel déguisé.
        Livewire::test(ListInstallationRates::class)
            ->call('updateTableColumnState', 'rate_per_m2', (string) $rate->getKey(), '275');

        $this->assertSame('275.00', $rate->fresh()->rate_per_m2);
        $this->assertSame(275.0, InstallationRate::rateFor('cuisine'));
    }

    public function test_le_tableau_refuse_un_tarif_negatif(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $rate = $this->rate('cuisine', 220);

        Livewire::test(ListInstallationRates::class)
            ->call('updateTableColumnState', 'rate_per_m2', (string) $rate->getKey(), '-50');

        $this->assertSame('220.00', $rate->fresh()->rate_per_m2);
    }

    public function test_lactivation_se_bascule_depuis_le_tableau(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $rate = $this->rate('cuisine', 220);

        Livewire::test(ListInstallationRates::class)
            ->call('updateTableColumnState', 'is_active', (string) $rate->getKey(), false);

        $this->assertFalse($rate->fresh()->is_active);
    }

    public function test_deux_tarifs_ne_peuvent_pas_viser_la_meme_application(): void
    {
        $this->rate('cuisine', 220);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->rate('cuisine', 300);
    }
}
