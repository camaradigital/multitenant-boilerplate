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
        // Tabela para as Comissões
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legislatura_id')->constrained('legislaturas')->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['Permanente', 'Temporária', 'Especial'])->default('Permanente');
            $table->string('email_contato')->nullable()->comment('E-mail oficial da comissão, se houver.');
            $table->timestamps();
        });

        // Tabela para relacionar Políticos às Comissões (Membros)
        Schema::create('comissao_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissao_id')->constrained('comissoes')->onDelete('cascade');
            $table->foreignId('politico_id')->constrained('politicos')->onDelete('cascade');
            $table->enum('cargo', ['Presidente', 'Vice-Presidente', 'Relator', 'Membro'])->default('Membro');
            $table->timestamps();

            // Garante que um político só possa ter um cargo por comissão
            $table->unique(['comissao_id', 'politico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comissao_membros');
        Schema::dropIfExists('comissoes');
    }
};
