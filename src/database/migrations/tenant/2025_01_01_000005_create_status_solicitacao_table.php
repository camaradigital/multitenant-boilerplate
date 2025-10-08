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
        Schema::create('status_solicitacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cor', 7)->default('#cccccc'); // Cor para UI
            $table->boolean('is_default_abertura')->default(false); // Marca o status inicial de uma nova solicitação
            $table->boolean('is_final')->default(false); // Marca um status como de conclusão (ex: "Finalizado", "Cancelado")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_solicitacao');
    }
};
