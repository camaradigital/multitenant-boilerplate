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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // O nome do campo que aparece no formulário (ex: "Número do NIS")
            $table->string('name')->unique(); // O nome da chave no JSON (ex: "numero_nis"), sem espaços ou acentos
            $table->string('type'); // Tipo do campo: text, number, date, select
            $table->json('options')->nullable(); // Para campos do tipo 'select', armazena as opções
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
