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
        // Verifica se a tabela 'users' existe antes de tentar alterá-la.
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Adiciona a nova coluna 'bairro', que pode ser nula.
                // O 'index()' cria um índice no banco de dados, o que acelera drasticamente as buscas e agrupamentos futuros nesta coluna.
                // 'after('profile_data')' posiciona a coluna logo após a de profile_data, para organização.
                $table->string('bairro')->nullable()->after('profile_data')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'bairro')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('bairro');
            });
        }
    }
};
