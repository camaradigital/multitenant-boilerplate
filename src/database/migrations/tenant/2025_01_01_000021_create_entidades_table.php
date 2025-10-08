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
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('categoria'); // Ex: ONG, Instituição de Ensino, Defesa Animal
            $table->string('cnpj')->nullable()->unique();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->text('endereco')->nullable();
            $table->text('descricao')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('registrado_por_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
