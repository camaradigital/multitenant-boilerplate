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
        Schema::connection('central')->create('tenants', function (Blueprint $table) {
            $table->id();
            // Informações de identificação
            $table->string('name'); // Alinhado com o esboço e modelo
            $table->string('cnpj', 18)->unique(); // CNPJ formatado, não nullable
            $table->string('subdomain')->unique(); // Alinhado com SubdomainTenantFinder
            $table->string('database_name')->unique(); // Nome do banco (ex: cac_camara-sp)
            $table->string('admin_email'); // Adicionado do esboço

            // Informações de endereço
            $table->string('endereco_cep', 9)->nullable();
            $table->string('endereco_logradouro')->nullable();
            $table->string('endereco_numero')->nullable();
            $table->string('endereco_complemento')->nullable();
            $table->string('endereco_bairro')->nullable();
            $table->string('endereco_cidade')->nullable();
            $table->string('endereco_estado', 2)->nullable();

            // Informações de personalização
            $table->string('logotipo_url')->nullable();
            $table->string('site_url')->nullable();
            $table->string('cor_primaria', 7)->default('#000000');
            $table->string('cor_secundaria', 7)->default('#FFFFFF');
            $table->json('data')->nullable(); // Configurações flexíveis

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenants');
    }
};
