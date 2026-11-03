<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Devis;
use App\Models\Product;
use App\Notifications\NewDevisReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Protections du formulaire de devis : leads, anti-spam, validation, alertes.
 */
class DevisProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function demande(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Youssef Bennani',
            'phone' => '0661219409',
            'application' => 'cuisine',
            'surface_m2' => 12,
        ], $overrides);
    }

    public function test_deux_demandes_successives_creent_deux_fiches(): void
    {
        $this->post('/devis', $this->demande(['message' => 'Cuisine']))->assertSessionHas('success');
        $this->post('/devis', $this->demande(['message' => 'Escalier', 'application' => 'escalier']))->assertSessionHas('success');

        $this->assertDatabaseCount('devis', 2);
        $this->assertSame(['Cuisine', 'Escalier'], Devis::orderBy('id')->pluck('message')->all());
    }

    public function test_le_champ_piege_bloque_les_robots(): void
    {
        $this->post('/devis', $this->demande(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->postJson('/devis/pdf', $this->demande(['website' => 'https://spam.example']))
            ->assertStatus(422);

        $this->assertDatabaseCount('devis', 0);
    }

    public function test_le_champ_piege_vide_est_accepte(): void
    {
        $this->post('/devis', $this->demande(['website' => '']))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('devis', 1);
    }

    #[DataProvider('numerosValides')]
    public function test_les_numeros_marocains_sont_acceptes_et_normalises(string $saisi, string $enregistre): void
    {
        $this->post('/devis', $this->demande(['phone' => $saisi]))->assertSessionHasNoErrors();

        $this->assertSame($enregistre, Devis::first()->phone);
    }

    public static function numerosValides(): array
    {
        return [
            'mobile 06' => ['0661219409', '0661219409'],
            'mobile 07' => ['0712345678', '0712345678'],
            'fixe 05' => ['0522123456', '0522123456'],
            'espaces' => ['06 61 21 94 09', '0661219409'],
            'points' => ['06.61.21.94.09', '0661219409'],
            'international' => ['+212 6 61 21 94 09', '+212661219409'],
            'international 00' => ['00212661219409', '+212661219409'],
            'international avec 0' => ['+212 (0)6 61 21 94 09', '+212661219409'],
        ];
    }

    #[DataProvider('numerosInvalides')]
    public function test_les_numeros_invalides_sont_refuses(string $saisi): void
    {
        $this->post('/devis', $this->demande(['phone' => $saisi]))->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('devis', 0);
    }

    public static function numerosInvalides(): array
    {
        return [
            'trop court' => ['061234'],
            'trop long' => ['066121940912'],
            'préfixe 08' => ['0812345678'],
            'étranger' => ['+33612345678'],
            'lettres' => ['06abcdefgh'],
        ];
    }

    public function test_une_application_inconnue_est_refusee(): void
    {
        $this->post('/devis', $this->demande(['application' => 'piscine-olympique']))
            ->assertSessionHasErrors('application');
    }

    public function test_le_statut_ne_peut_pas_etre_impose_par_le_visiteur(): void
    {
        $this->post('/devis', $this->demande(['status' => 'completed']))->assertSessionHasNoErrors();

        $this->assertSame('pending', Devis::first()->status);
    }

    public function test_au_dela_de_cinq_demandes_par_minute_le_visiteur_est_freine(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/devis', $this->demande(['client_name' => "Client $i"]))->assertRedirect();
        }

        $this->post('/devis', $this->demande())->assertStatus(429);
        $this->assertDatabaseCount('devis', 5);
    }

    public function test_le_gerant_est_alerte_par_email(): void
    {
        Notification::fake();
        $this->withoutDefer();

        $category = Category::create(['name' => 'Granit', 'slug' => 'granit']);
        $product = Product::create(['name' => 'Granit Noir Absolu', 'slug' => 'gna', 'category_id' => $category->id, 'price_per_m2' => 950]);

        $this->post('/devis', $this->demande(['product_id' => $product->id]))->assertSessionHasNoErrors();

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewDevisReceived::class,
            function (NewDevisReceived $notification, array $channels, AnonymousNotifiable $notifiable) {
                $mail = $notification->toMail($notifiable);

                return $notifiable->routes['mail'] === config('company.notification_email')
                    && str_contains($mail->subject, 'Youssef Bennani')
                    && collect($mail->introLines)->contains(fn ($line) => str_contains($line, 'Granit Noir Absolu'));
            }
        );
    }

    public function test_ladresse_dalerte_nest_pas_envoyee_au_navigateur(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page->missing('company.notification_email'));
    }

    public function test_le_message_de_confirmation_suit_la_langue(): void
    {
        $this->get('/langue/ar');

        $this->post('/devis', $this->demande())
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'شكرا Youssef Bennani'));
    }
}
