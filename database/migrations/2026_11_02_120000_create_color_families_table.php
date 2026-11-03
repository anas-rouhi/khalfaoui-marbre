<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Teintes du filtre catalogue, jusqu'ici figées dans le code
 * (Product::COLOR_FAMILIES et CatalogueSection.vue) : elles deviennent
 * administrables depuis le back-office.
 *
 * `products.color_family` garde la clé texte (« noir », « blanc »…) : les
 * fiches existantes restent rattachées sans reprise de données.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('color_families', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('swatch', 20)->default('#8b8e94');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Les six teintes proposées jusqu'ici, avec leurs pastilles d'origine.
        $now = now();

        DB::table('color_families')->insert(array_map(fn (array $family) => $family + [
            'created_at' => $now,
            'updated_at' => $now,
        ], [
            ['key' => 'noir', 'name' => 'Noir', 'name_ar' => 'أسود', 'swatch' => '#141519', 'sort_order' => 1],
            ['key' => 'blanc', 'name' => 'Blanc', 'name_ar' => 'أبيض', 'swatch' => '#eceae5', 'sort_order' => 2],
            ['key' => 'gris', 'name' => 'Gris', 'name_ar' => 'رمادي', 'swatch' => '#8b8e94', 'sort_order' => 3],
            ['key' => 'beige', 'name' => 'Beige', 'name_ar' => 'بيج', 'swatch' => '#cbb094', 'sort_order' => 4],
            ['key' => 'brun', 'name' => 'Brun', 'name_ar' => 'بني', 'swatch' => '#6b4a33', 'sort_order' => 5],
            ['key' => 'vert', 'name' => 'Vert', 'name_ar' => 'أخضر', 'swatch' => '#1e9d6b', 'sort_order' => 6],
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('color_families');
    }
};
