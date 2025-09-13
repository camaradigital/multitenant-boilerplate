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
        Schema::table('tenants', function (Blueprint $table) {
            // Armazena o valor máximo de renda familiar para acesso a serviços jurídicos.
            // Usamos decimal para valores monetários. Default para 2 salários mínimos de 2024.
            $table->decimal('limite_renda_juridico', 10, 2)->nullable()->default(2824.00)->after('permite_cadastro_cidade_externa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('limite_renda_juridico');
        });
    }
};
