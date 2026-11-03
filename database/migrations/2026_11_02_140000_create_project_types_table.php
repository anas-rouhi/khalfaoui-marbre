<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Types de projet des réalisations (Villa, Appartement, Hôtel…), jusqu'ici
 * figés dans ProjectResource::CATEGORIES et stockés sous forme de texte
 * français (« Hôtel ») : ils deviennent administrables et traduisibles.
 *
 * `projects.category` contient désormais la clé du type (« hotel »). Les
 * valeurs existantes sont converties ; une valeur inconnue devient un type
 * à part entière plutôt que d'être perdue.
 *
 * Ajoute aussi `projects.is_published` : une réalisation peut être masquée
 * du site sans être supprimée.
 */
return new class extends Migration
{
    private const DEFAULTS = [
        ['key' => 'villa', 'name' => 'Villa', 'name_ar' => 'فيلا'],
        ['key' => 'appartement', 'name' => 'Appartement', 'name_ar' => 'شقة'],
        ['key' => 'residence', 'name' => 'Résidence', 'name_ar' => 'إقامة'],
        ['key' => 'hotel', 'name' => 'Hôtel', 'name_ar' => 'فندق'],
        ['key' => 'restaurant', 'name' => 'Restaurant', 'name_ar' => 'مطعم'],
        ['key' => 'bureau', 'name' => 'Bureau', 'name_ar' => 'مكتب'],
        ['key' => 'showroom', 'name' => 'Showroom', 'name_ar' => 'صالة عرض'],
    ];

    public function up(): void
    {
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        foreach (self::DEFAULTS as $index => $type) {
            DB::table('project_types')->insert($type + [
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Texte français → clé du type.
        $existing = DB::table('projects')->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->pluck('category');

        foreach ($existing as $value) {
            $key = Str::slug($value);

            if (! DB::table('project_types')->where('key', $key)->exists()) {
                DB::table('project_types')->insert([
                    'key' => $key,
                    'name' => $value,
                    'sort_order' => (int) DB::table('project_types')->max('sort_order') + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('projects')->where('category', $value)->update(['category' => $key]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        // Clé → texte français, comme avant.
        foreach (DB::table('project_types')->get() as $type) {
            DB::table('projects')->where('category', $type->key)->update(['category' => $type->name]);
        }

        Schema::dropIfExists('project_types');
    }
};
