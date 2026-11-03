<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Project;
use App\Support\MediaPath;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SitemapController extends Controller
{
    /**
     * Plan du site pour les moteurs de recherche.
     *
     * Le site tient sur une seule page, déclinée en français et en arabe
     * (`?lang=ar`). Les fiches marbre n'ayant pas encore d'URL propre, elles
     * sont déclarées comme images de la page d'accueil (extension « image » du
     * protocole sitemap) : leur nom remonte ainsi dans Google Images.
     */
    public function __invoke(): Response
    {
        $alternates = Seo::alternates();

        return response()
            ->view('sitemap', [
                // Une entrée par langue, x-default exclu (même URL que le français).
                'pages' => array_values(Arr::except($alternates, 'x-default')),
                'alternates' => $alternates,
                'images' => $this->images(),
                'lastModified' => $this->lastModified(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return Collection<int, array{loc: string, title: string}>
     */
    private function images(): Collection
    {
        try {
            $products = Product::with('images')->orderBy('name')->get()->flatMap(
                fn (Product $product) => $product->images->map(fn ($image) => [
                    'path' => $image->image_path,
                    'title' => $product->name,
                ])
            );

            $projects = Project::published()->orderBy('sort_order')->get()->map(fn (Project $project) => [
                'path' => $project->cover_image,
                'title' => $project->title,
            ]);
        } catch (Throwable $e) {
            Log::error('Sitemap : catalogue indisponible.', ['exception' => $e]);

            return collect();
        }

        return $products->concat($projects)
            ->map(fn (array $image) => [
                'loc' => MediaPath::thumbnail($image['path']),
                'title' => $image['title'],
            ])
            ->filter(fn (array $image) => filled($image['loc']))
            ->unique('loc')
            ->values();
    }

    private function lastModified(): string
    {
        try {
            $latest = collect([Product::max('updated_at'), Project::max('updated_at')])->filter()->max();
        } catch (Throwable) {
            $latest = null;
        }

        return ($latest ? Carbon::parse($latest) : now())->toAtomString();
    }
}
