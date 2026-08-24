<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarifs de pose (main d'œuvre) au m², par type d'application.
     *
     * Ils étaient figés dans le composant d'estimation ; ils deviennent
     * modifiables depuis le back-office pour suivre les cours de la
     * main d'œuvre sans intervention technique.
     */
    public function up(): void
    {
        Schema::create('installation_rates', function (Blueprint $table) {
            $table->id();

            // Même vocabulaire que App\Models\Product::APPLICATIONS
            // (« cuisine », « sol », « salle-de-bain », « facade », « escalier »).
            $table->string('application')->unique();
            $table->string('label');
            $table->decimal('rate_per_m2', 8, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_rates');
    }
};
