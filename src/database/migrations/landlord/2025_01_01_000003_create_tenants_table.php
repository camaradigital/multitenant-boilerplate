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
            $table->string('name');
            $table->string('cnpj', 18)->unique();
            $table->string('subdomain')->unique();
            $table->string('database_name')->unique();
            $table->string('admin_email');

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

            // Campos de Contato (Consolidado)
            $table->string('telefone_contato')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email_contato')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();

            // Documentos Legais (Consolidado)
            $table->longText('terms_of_service')->nullable();
            $table->longText('privacy_policy')->nullable();

            // Configurações da Aplicação (Consolidado)
            $table->boolean('permite_cadastro_cidade_externa')->default(true);
            $table->decimal('limite_renda_juridico', 10, 2)->nullable()->default(2824.00);
            $table->boolean('exigir_renda_juridico')->default(true);

            // Configurações de Links Públicos (Consolidado)
            $table->boolean('publicar_achados_e_perdidos')->default(true);
            $table->boolean('publicar_pessoas_desaparecidas')->default(true);
            $table->boolean('publicar_memoria_legislativa')->default(true);
            $table->boolean('publicar_vagas_emprego')->default(true);

            // Configurações flexíveis
            $table->json('data')->nullable();

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
