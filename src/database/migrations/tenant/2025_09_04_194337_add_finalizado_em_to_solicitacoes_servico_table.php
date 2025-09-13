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
        // Adiciona a nova coluna 'finalizado_em' na tabela de solicitações.
        // Ela será nula por padrão, até que um atendimento seja finalizado.
        Schema::table('solicitacoes_servico', function (Blueprint $table) {
            $table->timestamp('finalizado_em')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitacoes_servico', function (Blueprint $table) {
            $table->dropColumn('finalizado_em');
        });
    }
};
