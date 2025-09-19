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
            // Adiciona as colunas para controlar a visibilidade dos módulos no portal público.
            $table->boolean('publicar_achados_e_perdidos')->default(true)->after('exigir_renda_juridico');
            $table->boolean('publicar_pessoas_desaparecidas')->default(true)->after('publicar_achados_e_perdidos');
            $table->boolean('publicar_memoria_legislativa')->default(true)->after('publicar_pessoas_desaparecidas');
            $table->boolean('publicar_vagas_emprego')->default(true)->after('publicar_memoria_legislativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Remove as colunas em ordem reversa para segurança.
            $table->dropColumn([
                'publicar_achados_e_perdidos',
                'publicar_pessoas_desaparecidas',
                'publicar_memoria_legislativa',
                'publicar_vagas_emprego',
            ]);
        });
    }
};
