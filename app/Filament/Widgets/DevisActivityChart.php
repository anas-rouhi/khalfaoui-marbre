<?php

namespace App\Filament\Widgets;

use App\Models\Devis;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

/**
 * Demandes de devis reçues, par jour (30 derniers jours) ou par mois
 * (12 derniers mois).
 */
class DevisActivityChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected static ?string $pollingInterval = null;

    public ?string $filter = '30d';

    /**
     * Or « pierre » : seul ton validé (luminosité, chroma, contraste) à la
     * fois sur le fond clair et sur le fond sombre du panneau.
     */
    public const COLOR = '#b98a3c';

    public function getHeading(): string|Htmlable|null
    {
        return __('Évolution des demandes de devis');
    }

    public function getDescription(): string|Htmlable|null
    {
        $total = array_sum($this->series()['counts']);

        return $this->filter === '12m'
            ? trans_choice('{0} Aucune demande sur 12 mois|{1} :count demande sur 12 mois|[2,*] :count demandes sur 12 mois', $total)
            : trans_choice('{0} Aucune demande sur 30 jours|{1} :count demande sur 30 jours|[2,*] :count demandes sur 30 jours', $total);
    }

    protected function getFilters(): ?array
    {
        return [
            '30d' => __('30 derniers jours'),
            '12m' => __('12 derniers mois'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = $this->series();

        return [
            'datasets' => [
                [
                    'label' => __('Demandes de devis'),
                    'data' => $series['counts'],
                    'backgroundColor' => self::COLOR,
                    'hoverBackgroundColor' => '#a07530',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                    'borderSkipped' => 'start',
                    'maxBarThickness' => 28,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getOptions(): array
    {
        $rtl = __('filament-panels::layout.direction') === 'rtl';

        return [
            'plugins' => [
                // Une seule série : le titre la nomme, pas besoin de légende.
                'legend' => ['display' => false],
                'tooltip' => ['rtl' => $rtl, 'displayColors' => false],
            ],
            'scales' => [
                'x' => [
                    // Le temps se lit de droite à gauche en arabe.
                    'reverse' => $rtl,
                    'grid' => ['display' => false],
                    'ticks' => ['maxRotation' => 0, 'autoSkipPadding' => 12],
                ],
                'y' => [
                    'position' => $rtl ? 'right' : 'left',
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'border' => ['display' => false],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.18)'],
                ],
            ],
        ];
    }

    /**
     * Libellés et nombre de demandes de chaque période, dans l'ordre
     * chronologique.
     *
     * @return array{labels: array<int, string>, counts: array<int, int>}
     */
    protected function series(): array
    {
        $monthly = $this->filter === '12m';

        $periods = $monthly
            ? collect(range(11, 0))->map(fn (int $ago) => now()->startOfMonth()->subMonths($ago))
            : collect(range(29, 0))->map(fn (int $ago) => now()->startOfDay()->subDays($ago));

        $key = fn (Carbon $date) => $date->format($monthly ? 'Y-m' : 'Y-m-d');

        $counts = Devis::where('created_at', '>=', $periods->first())
            ->pluck('created_at')
            ->countBy($key);

        return [
            'labels' => $periods->map(fn (Carbon $date) => $monthly
                ? $date->translatedFormat('M Y')
                : $date->translatedFormat('j M'))->all(),
            'counts' => $periods->map(fn (Carbon $date) => $counts->get($key($date), 0))->all(),
        ];
    }
}
