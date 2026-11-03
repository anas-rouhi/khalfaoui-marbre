<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ColorFamily;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectType;
use App\Support\MediaPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'products' => $this->safely('products', fn () => $this->products()),
            'projects' => $this->safely('projects', fn () => $this->projects()),
            // Distingue « base vide » (portfolio de démonstration) de « toutes
            // les réalisations masquées » (aucune ne doit apparaître).
            'hasPortfolio' => $this->hasPortfolio(),
            // Filtre des réalisations, administré depuis Catalogue › Types de projet.
            'projectTypes' => $this->safely('projectTypes', fn () => $this->projectTypes()),
            'categories' => $this->safely('categories', fn () => $this->categories()),
            // Filtres du catalogue, administrés depuis Réglages › Teintes et
            // Réglages › Usages & tarifs. Le composant n'affiche que ceux
            // portés par au moins une fiche.
            'colorFamilies' => $this->safely('colorFamilies', fn () => $this->colorFamilies()),
            'applications' => $this->safely('applications', fn () => $this->applications()),
            // Tarifs de main d'œuvre pilotés depuis le back-office.
            'installationRates' => $this->safely('installationRates', fn () => $this->installationRates()),
        ]);
    }

    /**
     * Exécute une requête catalogue en tolérant une base injoignable.
     *
     * La vitrine est d'abord une page de présentation : en environnement
     * serverless (base absente, credentials manquants, SQLite sur un disque
     * en lecture seule) elle doit s'afficher amputée de son catalogue plutôt
     * que de renvoyer un 500. L'incident part sur stderr pour rester visible
     * dans les Runtime Logs.
     *
     * @param  callable(): Collection<int, mixed>  $query
     * @return Collection<int, mixed>
     */
    private function safely(string $dataset, callable $query): Collection
    {
        try {
            return $query();
        } catch (Throwable $e) {
            Log::error("Chargement de « $dataset » impossible : la vitrine est rendue sans ces données.", [
                'exception' => $e,
            ]);

            return collect();
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function products(): Collection
    {
        return Product::with(['images', 'category'])
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->translated('name'),
                'slug' => $product->slug,
                'category' => $product->category?->translated('name'),
                // Clé du filtre « Famille » (slug de la catégorie).
                'categoryKey' => $product->category?->slug,
                'color' => $product->translated('color'),
                'colorFamily' => $product->color_family,
                'origin' => $product->origin,
                'finish' => $product->finish,
                'description' => $product->translated('description'),
                'featured' => $product->featured,
                'applications' => $product->applications ?? [],
                'pricePerM2' => $product->price_per_m2 !== null ? (float) $product->price_per_m2 : null,
                'image' => $this->imageUrl(
                    $product->images->firstWhere('is_main', true)?->image_path
                        ?? $product->images->first()?->image_path
                ),
                'gallery' => $product->images
                    ->map(fn ($image) => $this->imageUrl($image->image_path))
                    ->filter()
                    ->values(),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function projects(): Collection
    {
        // Seules les réalisations publiées : une réalisation masquée depuis
        // le back-office disparaît aussi des filtres par type.
        return Project::published()
            ->with(['images', 'projectType'])
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'title' => $project->translated('title'),
                // Nom du type dans la langue de la page ; la valeur brute si
                // le type a disparu de la liste.
                'category' => $project->projectType?->translated('name') ?? $project->category,
                // Clé du filtre par type de projet.
                'categoryKey' => $project->category,
                'location' => $project->location,
                'year' => $project->year,
                'description' => $project->translated('description'),
                'image' => $this->imageUrl($project->cover_image),
                'beforeImage' => $this->imageUrl($project->before_image),
                'beforeCaption' => $project->before_caption,
                'gallery' => $project->images
                    ->map(fn ($image) => [
                        'image' => $this->imageUrl($image->image_path),
                        'caption' => $image->caption,
                    ])
                    ->filter(fn (array $image) => filled($image['image']))
                    ->values(),
            ]);
    }

    /** Base injoignable : traitée comme vide, le portfolio de démonstration s'affiche. */
    private function hasPortfolio(): bool
    {
        try {
            return Project::exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return Collection<int, array{key: string, label: string}>
     */
    private function projectTypes(): Collection
    {
        return ProjectType::query()->ordered()->get()->map(fn (ProjectType $type) => [
            'key' => $type->key,
            'label' => $type->translated('name'),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function categories(): Collection
    {
        // Même forme que les teintes et les usages : le composant n'affiche que
        // les familles rattachées à au moins une fiche.
        return Category::query()->ordered()->get()->map(fn (Category $category) => [
            'key' => $category->slug,
            'label' => $category->translated('name'),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function installationRates(): Collection
    {
        return InstallationRate::active()
            ->map(fn (InstallationRate $rate) => [
                'key' => $rate->application,
                'label' => $rate->translated('label'),
                'ratePerM2' => (float) $rate->rate_per_m2,
            ]);
    }

    /**
     * @return Collection<int, array{key: string, label: string, swatch: string}>
     */
    private function colorFamilies(): Collection
    {
        return ColorFamily::query()->ordered()->get()->map(fn (ColorFamily $family) => [
            'key' => $family->key,
            'label' => $family->translated('name'),
            'swatch' => $family->swatch,
        ]);
    }

    /**
     * Tous les usages, actifs ou non dans l'estimateur : le filtre du
     * catalogue doit pouvoir afficher chaque usage coché sur une fiche.
     *
     * @return Collection<int, array{key: string, label: string}>
     */
    private function applications(): Collection
    {
        return InstallationRate::query()->orderBy('sort_order')->orderBy('label')->get()
            ->map(fn (InstallationRate $rate) => [
                'key' => $rate->application,
                'label' => $rate->translated('label'),
            ]);
    }

    private function imageUrl(?string $path): ?string
    {
        return MediaPath::url($path);
    }
}
