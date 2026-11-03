<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ProjectTypeResource\Pages\CreateProjectType;
use App\Filament\Resources\ProjectTypeResource\Pages\EditProjectType;
use App\Filament\Resources\ProjectTypeResource\Pages\ListProjectTypes;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Types de projet des réalisations : administrés en base, traduits, et
 * seuls les types portés par une réalisation publiée deviennent des filtres.
 */
class ProjectTypesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Storage::fake('public');

        return User::factory()->create(['is_admin' => true]);
    }

    private function project(array $attributes = []): Project
    {
        return Project::create(array_merge([
            'title' => 'Réalisation '.uniqid(),
            'category' => 'villa',
            'cover_image' => '/images/realisations/anfa-calacatta-cuisine.jpg',
        ], $attributes));
    }

    public function test_les_types_dorigine_sont_en_base_avec_leur_traduction(): void
    {
        $this->assertSame(
            ['villa', 'appartement', 'residence', 'hotel', 'restaurant', 'bureau', 'showroom'],
            ProjectType::query()->ordered()->pluck('key')->all()
        );
        $this->assertSame('فندق', ProjectType::firstWhere('key', 'hotel')->name_ar);
    }

    public function test_la_page_recoit_les_types_et_la_cle_de_chaque_realisation(): void
    {
        $this->project(['category' => 'hotel']);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('projectTypes', 7)
            ->where('projectTypes.0', ['key' => 'villa', 'label' => 'Villa'])
            ->where('projects.0.categoryKey', 'hotel')
            ->where('projects.0.category', 'Hôtel')
            ->where('hasPortfolio', true)
        );
    }

    public function test_types_et_realisations_sont_traduits_en_arabe(): void
    {
        $this->project(['category' => 'hotel']);

        $this->get('/?lang=ar')->assertInertia(fn ($page) => $page
            ->where('projectTypes.0.label', 'فيلا')
            ->where('projects.0.category', 'فندق')
        );
    }

    public function test_une_realisation_masquee_disparait_du_site(): void
    {
        $this->project(['title' => 'Visible', 'category' => 'villa']);
        $this->project(['title' => 'Masquée', 'category' => 'restaurant', 'is_published' => false]);

        // La réalisation masquée n'est pas envoyée : le filtre « Restaurant »,
        // construit à partir des réalisations reçues, ne peut pas apparaître.
        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('projects', 1)
            ->where('projects.0.title', 'Visible')
        );
    }

    public function test_tout_masquer_ne_fait_pas_reapparaitre_la_demonstration(): void
    {
        $this->project(['is_published' => false]);

        $this->get('/')->assertInertia(fn ($page) => $page
            ->has('projects', 0)
            ->where('hasPortfolio', true)
        );
    }

    public function test_base_vide_portfolio_de_demonstration(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page->where('hasPortfolio', false));
    }

    public function test_un_type_cree_dans_ladmin_devient_un_filtre(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateProjectType::class)
            ->fillForm(['name' => 'Clinique', 'name_ar' => 'مصحة'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('project_types', ['key' => 'clinique', 'sort_order' => 8]);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Clinique Dar Salam — Sols en granit',
                'category' => 'clinique',
                'cover_image' => [UploadedFile::fake()->image('clinique.jpg')],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('projects.0.categoryKey', 'clinique')
            ->where('projectTypes.7', ['key' => 'clinique', 'label' => 'Clinique'])
        );
    }

    public function test_un_type_peut_etre_cree_depuis_la_realisation(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateProject::class)
            ->callFormComponentAction('category', 'createOption', [
                'name' => 'Spa & Hammam',
                'key' => 'spa-hammam',
                'name_ar' => 'حمام وسبا',
            ])
            ->assertHasNoFormComponentActionErrors()
            ->assertFormSet(['category' => 'spa-hammam']);

        $this->assertDatabaseHas('project_types', ['key' => 'spa-hammam', 'name_ar' => 'حمام وسبا']);
    }

    public function test_la_publication_se_gere_depuis_le_tableau(): void
    {
        $this->actingAs($this->admin());
        $project = $this->project();

        Livewire::test(ListProjects::class)
            ->assertCanSeeTableRecords([$project])
            ->call('updateTableColumnState', 'is_published', $project->getKey(), false);

        $this->assertFalse($project->fresh()->is_published);
    }

    public function test_un_type_utilise_ne_peut_pas_etre_supprime_et_sa_cle_est_verrouillee(): void
    {
        $this->actingAs($this->admin());
        $this->project(['category' => 'villa']);

        Livewire::test(ListProjectTypes::class)
            ->assertTableActionHidden('delete', ProjectType::firstWhere('key', 'villa'))
            ->assertTableActionVisible('delete', ProjectType::firstWhere('key', 'bureau'));

        Livewire::test(EditProjectType::class, ['record' => ProjectType::firstWhere('key', 'villa')->getKey()])
            ->assertFormFieldIsDisabled('key');
    }

    public function test_la_page_types_de_projet_est_accessible(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/types-projet')->assertOk()->assertSee('Types de projet');
        $this->get('/admin/projects/create')->assertOk();
    }
}
