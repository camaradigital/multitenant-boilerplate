<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legislatura_id')->constrained('legislaturas')->onDelete('cascade');
            $table->foreignId('politico_id')->constrained('politicos')->onDelete('cascade');
            $table->string('cargo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandatos');
    }
};
