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
        Schema::create('sugestoes_projetos_lei', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo', 20)->unique();
            $table->string('cidadao_nome');
            $table->string('cidadao_email');
            $table->string('cidadao_telefone')->nullable();
            $table->string('titulo');
            $table->text('descricao');
            $table->foreignId('area_tematica_id')->nullable()->constrained('comissoes')->onDelete('set null')->comment('Vinculado ao ID da comissão para direcionamento');
            $table->string('anexo_path')->nullable();
            $table->enum('status', ['Recebida', 'Em Análise', 'Arquivada', 'Aprovada'])->default('Recebida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sugestoes_projetos_lei');
    }
};
