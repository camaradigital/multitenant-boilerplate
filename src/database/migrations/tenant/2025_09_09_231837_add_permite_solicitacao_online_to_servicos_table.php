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
        Schema::table('servicos', function (Blueprint $table) {
            // Adiciona a nova coluna após 'is_juridico'
            // O padrão (default) é 'false', garantindo que os serviços existentes
            // não se tornem online acidentalmente.
            $table->boolean('permite_solicitacao_online')->default(false)->after('is_juridico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            //
        });
    }
};
