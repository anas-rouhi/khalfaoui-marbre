<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_de_connexion_propose_le_mot_de_passe_oublie(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(route('filament.admin.auth.password-reset.request'), false);
    }

    public function test_la_page_de_demande_est_accessible(): void
    {
        $this->get('/admin/password-reset/request')->assertOk();
    }

    public function test_le_lien_de_reinitialisation_est_envoye_par_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['email' => 'gerant@example.com', 'is_admin' => true]);

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'gerant@example.com'])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo($admin, ResetPassword::class);
    }

    public function test_une_adresse_inconnue_ne_declenche_aucun_envoi(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'inconnu@example.com'])
            ->call('request');

        Notification::assertNothingSent();
    }
}
