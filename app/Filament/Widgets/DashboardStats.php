<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DevisResource;
use App\Filament\Resources\ProjectResource;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Les quatre indicateurs du tableau de bord : demandes à traiter, surface
 * demandée, matériau en vogue et chantiers en ligne.
 */
class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    /** Filament interroge par défaut toutes les 5 s : inutile ici. */
    protected static ?string $pollingInterval = '60s';

    /** Fenêtre de la « tendance du moment ». */
    public const TREND_DAYS = 30;

    protected function getStats(): array
    {
        return [
            $this->pendingStat(),
            $this->surfaceStat(),
            $this->trendingMaterialStat(),
            $this->publishedProjectsStat(),
        ];
    }

    private function pendingStat(): Stat
    {
        $pending = Devis::where('status', 'pending')->count();

        return Stat::make(__('Devis en attente'), $pending)
            ->description($pending > 0 ? __('À rappeler au plus vite') : __('Toutes les demandes sont traitées'))
            ->descriptionIcon($pending > 0 ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
            ->color($pending > 0 ? 'warning' : 'success')
            // Arrivées des 7 derniers jours, en courbe de fond.
            ->chart($this->dailyCounts(7))
            ->url(DevisResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]]));
    }

    private function surfaceStat(): Stat
    {
        $total = (float) Devis::sum('surface_m2');
        $thisMonth = (float) Devis::where('created_at', '>=', now()->startOfMonth())->sum('surface_m2');

        return Stat::make(__('Surface estimée demandée'), self::formatSurface($total).__(' m²'))
            ->description(__('+:surface m² ce mois-ci', ['surface' => self::formatSurface($thisMonth)]))
            ->descriptionIcon('heroicon-m-square-3-stack-3d')
            ->color('primary');
    }

    private function trendingMaterialStat(): Stat
    {
        $top = $this->mostRequestedProduct(now()->subDays(self::TREND_DAYS));
        $recent = $top !== null;
        // Pas de demande récente : on se rabat sur le classement de toujours.
        $top ??= $this->mostRequestedProduct();

        if ($top === null) {
            return Stat::make(__('Matériau le plus demandé'), '—')
                ->description(__('Aucune demande avec matériau'))
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('gray');
        }

        [$product, $count] = $top;

        return Stat::make(__('Matériau le plus demandé'), $product->translated('name'))
            ->description($recent
                ? trans_choice('{1} :count demande sur 30 jours|[2,*] :count demandes sur 30 jours', $count)
                : trans_choice('{1} :count demande au total|[2,*] :count demandes au total', $count))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('gold');
    }

    private function publishedProjectsStat(): Stat
    {
        $published = Project::published()->count();
        $drafts = Project::where('is_published', false)->count();

        return Stat::make(__('Réalisations en ligne'), $published)
            ->description(trans_choice('{0} Aucun brouillon en attente|{1} :count brouillon hors ligne|[2,*] :count brouillons hors ligne', $drafts))
            ->descriptionIcon('heroicon-m-globe-alt')
            ->color('primary')
            ->url(ProjectResource::getUrl('index'));
    }

    /**
     * Référence la plus citée dans les demandes, avec son nombre de demandes.
     *
     * @return array{0: Product, 1: int}|null
     */
    private function mostRequestedProduct($since = null): ?array
    {
        $row = Devis::query()
            ->whereNotNull('product_id')
            ->when($since, fn ($query) => $query->where('created_at', '>=', $since))
            ->selectRaw('product_id, count(*) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->orderBy('product_id')
            ->first();

        $product = $row ? Product::find($row->product_id) : null;

        return $product ? [$product, (int) $row->total] : null;
    }

    /**
     * Nombre de demandes reçues chaque jour, du plus ancien au plus récent.
     *
     * @return array<int, int>
     */
    private function dailyCounts(int $days): array
    {
        $counts = Devis::where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->toDateString());

        return collect(range($days - 1, 0))
            ->map(fn (int $ago) => $counts->get(now()->subDays($ago)->toDateString(), 0))
            ->all();
    }

    /** « 1 234,5 » : séparateurs français, décimale seulement si utile. */
    public static function formatSurface(float $value): string
    {
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 1;

        return number_format($value, $decimals, ',', DevisResource::THOUSANDS);
    }
}
