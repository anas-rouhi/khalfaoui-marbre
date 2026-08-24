<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Traductions arabes du contenu éditorial.
     *
     * Toutes les colonnes sont facultatives : lorsqu'une traduction manque,
     * le site affiche le texte français plutôt qu'un blanc. Le gérant peut
     * donc traduire progressivement, fiche par fiche.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('color_ar')->nullable()->after('color');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'color_ar', 'description_ar']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'description_ar']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
