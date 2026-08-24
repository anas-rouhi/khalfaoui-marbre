<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Usages possibles : sol, cuisine, salle-de-bain, facade, escalier
            $table->json('applications')->nullable()->after('finish');
            // Famille de teinte utilisée par les filtres du catalogue
            $table->string('color_family')->nullable()->after('color');
            $table->decimal('price_per_m2', 8, 2)->nullable()->after('applications');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->year('year')->nullable()->after('location');
            $table->unsignedInteger('sort_order')->default(0)->after('cover_image');
        });

        Schema::table('devis', function (Blueprint $table) {
            $table->string('location')->nullable()->after('email');
            $table->string('application')->nullable()->after('product_id');
            $table->decimal('estimated_total', 10, 2)->nullable()->after('surface_m2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['applications', 'color_family', 'price_per_m2']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['category', 'year', 'sort_order']);
        });

        Schema::table('devis', function (Blueprint $table) {
            $table->dropColumn(['location', 'application', 'estimated_total']);
        });
    }
};
