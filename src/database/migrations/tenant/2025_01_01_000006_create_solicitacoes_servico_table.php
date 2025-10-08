<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('servico_id')->constrained('servicos');
            $table->foreignId('atendente_id')->nullable()->constrained('users');
            $table->foreignId('status_id')->nullable()->constrained('status_solicitacao');
            $table->string('status')->default('Aguardando Atendimento'); // Mantido por compatibilidade com o dump, mas pode ser removido se `status_id` for obrigatório.
            $table->text('observacoes')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            $table->timestamp('finalizado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_servico');
    }
};
