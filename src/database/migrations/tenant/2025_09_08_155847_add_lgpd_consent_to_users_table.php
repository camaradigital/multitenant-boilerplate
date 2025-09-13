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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna para registrar o aceite dos Termos de Uso.
            // O campo é 'nullable' para não afetar usuários existentes.
            $table->timestamp('terms_accepted_at')->nullable()->after('remember_token');

            // Adiciona a coluna para registrar o aceite da Política de Privacidade.
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'privacy_accepted_at']);
        });
    }
};
