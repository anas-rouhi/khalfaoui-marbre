<?php

namespace Database\Seeders;

use App\Models\InstallationRate;
use Illuminate\Database\Seeder;

class InstallationRateSeeder extends Seeder
{
    /**
     * Pose les tarifs de référence sans jamais écraser ceux que le gérant
     * aurait déjà ajustés depuis le back-office : `firstOrCreate` ne touche
     * pas aux lignes existantes.
     */
    public function run(): void
    {
        foreach (InstallationRate::DEFAULTS as $rate) {
            InstallationRate::firstOrCreate(
                ['application' => $rate['application']],
                $rate
            );
        }
    }
}
