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
        // Usa Schema::table() para MODIFICAR a tabela existente
        Schema::connection('central')->table('tenants', function (Blueprint $table) {
            // Adicionando os novos campos de contato
            $table->string('telefone_contato')->nullable()->after('site_url');
            $table->string('whatsapp')->nullable()->after('telefone_contato');
            $table->string('email_contato')->nullable()->after('whatsapp');
            $table->string('instagram')->nullable()->after('email_contato');
            $table->string('youtube')->nullable()->after('instagram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('tenants', function (Blueprint $table) {
            // Remove as colunas caso precise reverter a migration
            $table->dropColumn([
                'telefone_contato',
                'whatsapp',
                'email_contato',
                'instagram',
                'youtube',
            ]);
        });
    }
};
