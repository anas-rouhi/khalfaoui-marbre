<?php

namespace App\Http\Controllers;

use App\Http\Requests\DevisPdfRequest;
use App\Http\Requests\StoreDevisRequest;
use App\Models\Devis;
use App\Models\InstallationRate;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DevisController extends Controller
{
    /** Clé de session mémorisant la demande en cours de constitution. */
    private const SESSION_KEY = 'devis.current_id';

    public function store(StoreDevisRequest $request): RedirectResponse
    {
        $devis = $this->persist($request->validated());

        return back()->with('success', "Merci {$devis->client_name}, votre demande de devis a bien été enregistrée. Notre équipe vous rappelle sous 24h.");
    }

    /**
     * Devis estimatif au format PDF.
     *
     * La demande est enregistrée au passage : télécharger un devis est un
     * signal d'achat au moins aussi fort que d'envoyer le formulaire.
     */
    public function pdf(DevisPdfRequest $request): Response
    {
        $devis = $this->persist($request->validated())->load('product.images');

        $product = $devis->product;
        $surface = (float) $devis->surface_m2;

        $materialRate = (float) ($product?->price_per_m2 ?? 0);
        $installationRate = InstallationRate::rateFor($devis->application);

        $pdf = Pdf::loadView('pdf.devis', [
            'devis' => $devis,
            'product' => $product,
            'surface' => $surface,
            'materialRate' => $materialRate,
            'materialCost' => $materialRate * $surface,
            'installationRate' => $installationRate,
            'installationCost' => $installationRate * $surface,
            'total' => $devis->estimated_total !== null
                ? (float) $devis->estimated_total
                : ($materialRate + $installationRate) * $surface,
            'applicationLabel' => InstallationRate::where('application', $devis->application)->value('label')
                ?? Product::APPLICATIONS[$devis->application] ?? '—',
            'thumbnail' => $this->thumbnailPath($product),
            // Variante matricielle sur fond sombre : dompdf ne sait pas rendre
            // le texte des SVG, et le lettrage du logo est blanc — il lui faut
            // donc son propre fond, identique à celui du bandeau.
            'logo' => public_path('images/brand/khalfaoui-marbre-logo-pdf.jpg'),
            'company' => config('company'),
        ])->setPaper('a4');

        return $pdf->download($devis->reference.'.pdf');
    }

    /**
     * Enregistre la demande, ou met à jour celle déjà créée dans la même
     * session : un visiteur qui télécharge son devis puis envoie le formulaire
     * ne doit pas générer deux fiches identiques côté back-office.
     */
    private function persist(array $data): Devis
    {
        $product = empty($data['product_id']) ? null : Product::find($data['product_id']);

        $data['estimated_total'] = $this->estimate(
            $product,
            $data['application'] ?? null,
            $data['surface_m2'] ?? null
        );

        $existing = session(self::SESSION_KEY)
            ? Devis::where('id', session(self::SESSION_KEY))->where('status', 'pending')->first()
            : null;

        if ($existing) {
            $existing->update($data);

            return $existing;
        }

        $devis = Devis::create($data);
        session([self::SESSION_KEY => $devis->id]);

        return $devis;
    }

    /**
     * Estimation recalculée côté serveur : le montant affiché par le
     * navigateur ne doit jamais être considéré comme fiable.
     *
     * Le calcul reprend exactement celui de l'estimateur — fourniture + pose —
     * pour que le montant enregistré soit celui que le client a vu à l'écran.
     */
    private function estimate(?Product $product, ?string $application, float|string|null $surface): ?float
    {
        $surface = (float) $surface;

        if ($surface <= 0 || ! $product?->price_per_m2) {
            return null;
        }

        $material = (float) $product->price_per_m2 * $surface;
        $installation = InstallationRate::rateFor($application) * $surface;

        return round($material + $installation, 2);
    }

    /**
     * Chemin disque de la vignette produit : dompdf lit les fichiers locaux,
     * pas les URL du site.
     *
     * La photo du catalogue fait 1200 px de large et serait embarquée telle
     * quelle dans le PDF (≈ 1 Mo pour une image affichée en 66 px). On en
     * garde donc une version réduite en cache.
     */
    private function thumbnailPath(?Product $product): ?string
    {
        $path = $product?->images->firstWhere('is_main', true)?->image_path
            ?? $product?->images->first()?->image_path;

        if (blank($path)) {
            return null;
        }

        $source = str_starts_with($path, '/')
            ? public_path(ltrim($path, '/'))
            : storage_path('app/public/'.$path);

        if (! is_file($source)) {
            return null;
        }

        return $this->cachedThumbnail($source) ?? $source;
    }

    /** Vignette 260 px de large, régénérée si la photo source a changé. */
    private function cachedThumbnail(string $source): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $directory = storage_path('app/pdf-thumbnails');
        $target = $directory.'/'.md5($source.filemtime($source)).'.jpg';

        if (is_file($target)) {
            return $target;
        }

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        $info = @getimagesize($source);

        $image = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => null,
        };

        if (! $image) {
            return null;
        }

        $width = 260;
        $height = (int) round(imagesy($image) * ($width / imagesx($image)));

        $resized = imagecreatetruecolor($width, $height);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        imagejpeg($resized, $target, 82);

        imagedestroy($image);
        imagedestroy($resized);

        return is_file($target) ? $target : null;
    }
}
