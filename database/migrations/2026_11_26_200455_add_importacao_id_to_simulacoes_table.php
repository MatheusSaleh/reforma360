<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simulação de uma nota importada (só os itens dela). Nulo = catálogo inteiro.
     */
    public function up(): void
    {
        Schema::table('simulacoes', function (Blueprint $table): void {
            $table->foreignId('importacao_id')->nullable()->after('user_id')->constrained('importacoes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('simulacoes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('importacao_id');
        });
    }
};
