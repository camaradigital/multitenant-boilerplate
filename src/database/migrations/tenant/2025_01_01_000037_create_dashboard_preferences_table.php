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
        Schema::create('dashboard_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('widget_identifier')->comment('Identificador único para o widget, ex: "metric.atendimentosHoje"');
            $table->boolean('is_visible')->default(true);
            $table->integer('order')->default(0);
            $table->json('settings')->nullable(); // Para configurações futuras, como tamanho do card
            $table->timestamps();

            $table->unique(['role_id', 'widget_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_preferences');
    }
};
