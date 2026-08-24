<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Photo « avant travaux » : le chantier brut, comparé à la finition posée
     * dans le comparateur avant/après du site. Facultative — seules les
     * réalisations qui en possèdent une apparaissent dans le comparateur.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('before_image')->nullable()->after('cover_image');
            $table->string('before_caption')->nullable()->after('before_image');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['before_image', 'before_caption']);
        });
    }
};
