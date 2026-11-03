<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class OptimizeImages extends Command
{
    protected $signature = 'marbre:optimize-images';

    protected $description = 'Générer les copies WebP des photos du site et des photos téléversées';

    public function handle(): int
    {
        $directories = array_filter([
            public_path('images'),
            storage_path('app/public'),
        ], 'is_dir');

        $files = Finder::create()->files()->in($directories)->name('/\.(jpe?g|png)$/i')
            // Le logo du PDF est lu par dompdf, qui n'a que faire du WebP.
            ->notPath('brand');

        $before = 0;
        $after = 0;
        $failed = 0;

        foreach ($files as $file) {
            $webp = ImageOptimizer::toWebp($file->getPathname());

            if ($webp === null) {
                $failed++;
                $this->components->warn("Ignoré : {$file->getRelativePathname()}");

                continue;
            }

            $before += $file->getSize();
            $after += filesize($webp);
        }

        $this->components->info(sprintf(
            '%d photo(s) converties : %s Ko → %s Ko (-%d %%).',
            count($files) - $failed,
            number_format($before / 1024, 0, ',', ' '),
            number_format($after / 1024, 0, ',', ' '),
            $before > 0 ? round(100 - $after * 100 / $before) : 0,
        ));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
