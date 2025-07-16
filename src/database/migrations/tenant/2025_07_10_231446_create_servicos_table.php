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
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_servico_id')->constrained('tipos_servico');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->boolean('is_active')->default(true);

            // Coluna para armazenar as regras de limite (ex: {"limite": 10, "periodo": "semanal"})
            $table->json('regras_limite')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
