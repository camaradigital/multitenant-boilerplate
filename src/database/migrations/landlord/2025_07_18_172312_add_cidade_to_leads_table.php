<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona a coluna 'cidade' na tabela 'leads'.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Adiciona a nova coluna 'cidade' do tipo string (VARCHAR)
            // ->nullable() permite que a coluna seja nula (opcional)
            // ->after('endereco') coloca a nova coluna logo depois da coluna 'endereco'
            $table->string('cidade')->nullable()->after('endereco');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Remove a coluna 'cidade' caso a migration seja revertida.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('cidade');
        });
    }
};
