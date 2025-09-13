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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_servico_id')->constrained('solicitacoes_servico')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quem fez o upload
            $table->string('nome_original'); // Nome do arquivo no computador do usuário
            $table->string('path'); // Caminho do arquivo no disco
            $table->string('mime_type');
            $table->unsignedBigInteger('tamanho'); // Tamanho em bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
