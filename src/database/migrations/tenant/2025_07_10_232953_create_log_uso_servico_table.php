<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create('log_uso_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('servico_id')->constrained('servicos')->onDelete('cascade');
            $table->foreignId('solicitacao_servico_id')->constrained('solicitacoes_servico')->onDelete('cascade');
            $table->timestamps(); // A data do log é o created_at
        });
    }

    public function down(): void {

        Schema::dropIfExists('log_uso_servico');

    }
};
