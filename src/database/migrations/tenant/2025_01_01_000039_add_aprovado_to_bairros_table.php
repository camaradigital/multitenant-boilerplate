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
        Schema::table('bairros', function (Blueprint $table) {
            // Adiciona a coluna 'aprovado' depois da coluna 'nome'
            // O padrão é 'true', então todos os bairros existentes são aprovados.
            $table->boolean('aprovado')->default(true)->after('nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bairros', function (Blueprint $table) {
            $table->dropColumn('aprovado');
        });
    }
};
