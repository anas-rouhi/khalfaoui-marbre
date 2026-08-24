<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InstallationRate;
use App\Models\Product;
use App\Models\Project;
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
            'categories' => $this->safely('categories', fn () => $this->categories()),
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
        return Project::with('images')
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'title' => $project->translated('title'),
                'category' => $project->category,
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function categories(): Collection
    {
        return Category::orderBy('name')->get()->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->translated('name'),
            'slug' => $category->slug,
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
                'label' => $rate->label,
                'ratePerM2' => (float) $rate->rate_per_m2,
            ]);
    }

    private function imageUrl(?string $path): ?string
    {
        return MediaPath::url($path);
    }
}
