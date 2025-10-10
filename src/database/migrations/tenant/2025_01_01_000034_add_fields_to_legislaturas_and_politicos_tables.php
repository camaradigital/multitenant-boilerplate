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
        Schema::table('legislaturas', function (Blueprint $table) {
            $table->boolean('is_atual')->default(false)->after('data_fim')->comment('Identifica se esta é a legislatura vigente.');
        });

        Schema::table('politicos', function (Blueprint $table) {
            $table->string('email')->nullable()->after('foto_path')->comment('Email de contato do político para notificações.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legislaturas', function (Blueprint $table) {
            $table->dropColumn('is_atual');
        });

        Schema::table('politicos', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
