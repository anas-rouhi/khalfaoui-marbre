<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les tarifs de pose deviennent la liste unique des usages (filtre du
 * catalogue, cases à cocher des fiches, estimateur) : un usage créé par le
 * gérant doit donc pouvoir porter son libellé arabe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installation_rates', function (Blueprint $table) {
            $table->string('label_ar')->nullable()->after('label');
        });

        // Libellés arabes déjà utilisés par le site pour les usages d'origine.
        $labels = [
            'sol' => 'أرضية',
            'cuisine' => 'سطح مطبخ',
            'salle-de-bain' => 'حمام',
            'facade' => 'واجهة',
            'escalier' => 'درج',
        ];

        foreach ($labels as $application => $label) {
            DB::table('installation_rates')
                ->where('application', $application)
                ->whereNull('label_ar')
                ->update(['label_ar' => $label]);
        }
    }

    public function down(): void
    {
        Schema::table('installation_rates', function (Blueprint $table) {
            $table->dropColumn('label_ar');
        });
    }
};
