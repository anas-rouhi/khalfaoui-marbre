<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les familles de matière (Marbre, Granit…) deviennent le troisième filtre
 * du catalogue : elles reçoivent un ordre d'affichage et, pour celles livrées
 * avec le site, leur nom arabe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
        });

        $families = [
            'marbre' => ['name_ar' => 'رخام', 'sort_order' => 1],
            'granit' => ['name_ar' => 'غرانيت', 'sort_order' => 2],
            'travertin-pierre' => ['name_ar' => 'ترافرتين وحجر', 'sort_order' => 3],
            'travertin' => ['name_ar' => 'ترافرتين', 'sort_order' => 3],
            'quartz' => ['name_ar' => 'كوارتز', 'sort_order' => 4],
            'pierre-naturelle' => ['name_ar' => 'حجر طبيعي', 'sort_order' => 5],
        ];

        foreach ($families as $slug => $values) {
            DB::table('categories')->where('slug', $slug)->update(['sort_order' => $values['sort_order']]);

            // Une traduction déjà saisie n'est jamais écrasée.
            DB::table('categories')->where('slug', $slug)->whereNull('name_ar')
                ->update(['name_ar' => $values['name_ar']]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
