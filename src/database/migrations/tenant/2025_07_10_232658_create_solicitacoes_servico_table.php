<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create('solicitacoes_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Cidadão que solicitou
            $table->foreignId('servico_id')->constrained('servicos'); // Serviço solicitado
            $table->foreignId('atendente_id')->nullable()->constrained('users'); // Funcionário que atendeu
            $table->string('status')->default('Aguardando Atendimento'); // Status do pedido
            $table->text('observacoes')->nullable(); // Observações do cidadão ou do atendente
            $table->timestamp('concluido_em')->nullable(); // Data da conclusão
            $table->timestamps();
        });
    }
    public function down(): void {

        Schema::dropIfExists('solicitacoes_servico');

    }
};
