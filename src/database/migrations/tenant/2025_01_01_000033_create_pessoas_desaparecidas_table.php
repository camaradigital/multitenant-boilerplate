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
        Schema::create('pessoas_desaparecidas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->integer('idade');
            $table->date('data_desaparecimento');
            $table->string('local_desaparecimento');
            $table->text('detalhes'); // Características físicas, roupas, etc.

            $table->string('foto_path'); // Caminho para a foto da pessoa
            $table->string('boletim_ocorrencia_path'); // Caminho para o B.O.

            $table->enum('status', ['Aguardando Aprovação', 'Publicado', 'Encontrado'])->default('Aguardando Aprovação');

            $table->foreignId('registrado_por_user_id')->constrained('users'); // Funcionário que cadastrou
            $table->foreignId('moderado_por_user_id')->nullable()->constrained('users'); // Funcionário que aprovou/rejeitou

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas_desaparecidas');
    }
};
