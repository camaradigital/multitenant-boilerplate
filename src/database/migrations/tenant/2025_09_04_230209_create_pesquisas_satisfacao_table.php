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
        Schema::create('pesquisas_satisfacao', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para a solicitação de serviço que está sendo avaliada.
            // Uma solicitação só pode ter uma avaliação, por isso a chave é única.
            $table->foreignId('solicitacao_servico_id')->constrained('solicitacoes_servico')->onDelete('cascade')->unique();

            // Chave estrangeira para o usuário (cidadão) que respondeu à pesquisa.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // A nota dada pelo cidadão (ex: de 1 a 5).
            $table->unsignedTinyInteger('nota');

            // O comentário opcional deixado pelo cidadão.
            $table->text('comentario')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesquisas_satisfacao');
    }
};
