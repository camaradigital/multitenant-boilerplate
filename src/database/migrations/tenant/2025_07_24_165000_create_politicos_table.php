<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->string('nome_politico');
            $table->string('partido')->nullable();
            $table->text('biografia')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicos');
    }
};
