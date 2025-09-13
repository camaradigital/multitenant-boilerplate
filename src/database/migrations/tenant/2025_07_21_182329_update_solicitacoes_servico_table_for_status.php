<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_servico', function (Blueprint $table) {
            // Remover a coluna de string antiga, se necessário, após migrar os dados
            // $table->dropColumn('status');

            // Adicionar a nova coluna de chave estrangeira
            $table->foreignId('status_id')->after('atendente_id')->nullable()->constrained('status_solicitacao');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_servico', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            // $table->string('status')->default('Aguardando Atendimento'); // Adicionar de volta se reverter
        });
    }
};
