<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['is_admin' => true], $attributes));
    }

    public function test_la_page_profil_est_accessible_a_ladministrateur(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/profile')
            ->assertOk()
            ->assertSee('Profil');
    }

    public function test_la_page_profil_est_refusee_a_un_non_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get('/admin/profile')->assertForbidden();
    }

    public function test_la_page_profil_exige_une_connexion(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
    }

    public function test_le_nom_et_lemail_sont_modifiables(): void
    {
        $user = $this->admin(['name' => 'Ancien nom', 'email' => 'ancien@exemple.com']);
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Khalfaoui',
                'email' => 'contact@khalfaoui-marbre.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('Khalfaoui', $user->name);
        $this->assertSame('contact@khalfaoui-marbre.com', $user->email);
    }

    public function test_le_mot_de_passe_est_modifiable_et_bien_hache(): void
    {
        $user = $this->admin(['password' => Hash::make('AncienMotDePasse1!')]);
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NouveauMotDePasse2!',
                'passwordConfirmation' => 'NouveauMotDePasse2!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertTrue(Hash::check('NouveauMotDePasse2!', $user->password));
        $this->assertFalse(Hash::check('AncienMotDePasse1!', $user->password));
    }

    public function test_un_mot_de_passe_laisse_vide_ne_change_rien(): void
    {
        $user = $this->admin(['password' => Hash::make('MotDePasseStable1!')]);
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Khalfaoui', 'email' => $user->email])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('MotDePasseStable1!', $user->refresh()->password));
    }

    public function test_la_confirmation_du_mot_de_passe_est_verifiee(): void
    {
        $user = $this->admin();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NouveauMotDePasse2!',
                'passwordConfirmation' => 'PasLeMeme3!',
            ])
            ->call('save')
            // Filament valide la concordance avec `->same()` sur le champ
            // « password » : c'est donc lui qui porte l'erreur.
            ->assertHasFormErrors(['password']);
    }

    public function test_un_email_deja_pris_est_refuse(): void
    {
        $this->admin(['email' => 'deja@exemple.com']);
        $user = $this->admin(['email' => 'moi@exemple.com']);
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => $user->name, 'email' => 'deja@exemple.com'])
            ->call('save')
            ->assertHasFormErrors(['email']);
    }

    public function test_le_profil_ne_permet_pas_de_sattribuer_les_droits_admin(): void
    {
        // Le formulaire ne contient que nom, e-mail et mot de passe : un
        // compte non administrateur ne peut pas s'octroyer l'accès au panneau.
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this->assertFalse($user->fresh()->is_admin);
        $this->get('/admin/profile')->assertForbidden();
    }
}
