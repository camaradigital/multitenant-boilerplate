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
        Schema::table('tenants', function (Blueprint $table) {
            // Usamos LONGTEXT para permitir textos grandes com formatação HTML.
            $table->longText('terms_of_service')->nullable()->after('cor_secundaria');
            $table->longText('privacy_policy')->nullable()->after('terms_of_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['terms_of_service', 'privacy_policy']);
        });
    }
};
