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
        Schema::create('funcionario_tipo_servico', function (Blueprint $table) {
            // Coluna para o ID do funcionário (da tabela 'users')
            $table->unsignedBigInteger('user_id');
            // Coluna para o ID do tipo de serviço (da tabela 'tipos_servico')
            $table->unsignedBigInteger('tipo_servico_id');

            // Definindo as chaves estrangeiras
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tipo_servico_id')->references('id')->on('tipos_servico')->onDelete('cascade');

            // Definindo uma chave primária composta para evitar duplicatas
            $table->primary(['user_id', 'tipo_servico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionario_tipo_servico');
    }
};
