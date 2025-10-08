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
        Schema::create('achados_e_perdidos_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento'); // Ex: RG, CPF, CNH
            $table->string('nome_completo'); // Nome que consta no documento
            $table->string('numero_documento')->nullable();
            $table->date('data_encontrado');
            $table->string('local_encontrado');
            $table->string('entregue_por'); // Nome de quem encontrou e entregou na Câmara

            $table->enum('status', ['Aguardando Retirada', 'Entregue'])->default('Aguardando Retirada');

            $table->text('observacoes')->nullable();

            // Campos preenchidos na devolução
            $table->date('data_entrega')->nullable();
            $table->string('retirado_por_nome')->nullable();
            $table->string('retirado_por_cpf')->nullable();

            $table->foreignId('registrado_por_user_id')->constrained('users'); // Funcionário que cadastrou
            $table->foreignId('entregue_por_user_id')->nullable()->constrained('users'); // Funcionário que devolveu

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achados_e_perdidos_documentos');
    }
};
