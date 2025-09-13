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
            $table->boolean('publicar_achados_e_perdidos')->default(true)->after('exigir_renda_juridico');
            $table->boolean('publicar_pessoas_desaparecidas')->default(true)->after('publicar_achados_e_perdidos');
            $table->boolean('publicar_memoria_legislativa')->default(true)->after('publicar_pessoas_desaparecidas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('publicar_achados_e_perdidos');
            $table->dropColumn('publicar_pessoas_desaparecidas');
            $table->dropColumn('publicar_memoria_legislativa');
        });
    }
};
