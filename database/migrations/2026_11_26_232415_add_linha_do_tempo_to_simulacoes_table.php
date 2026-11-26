<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulacoes', function (Blueprint $table): void {
            $table->foreignId('linha_do_tempo_id')->nullable()->after('importacao_id')->constrained('linhas_do_tempo')->cascadeOnDelete();
        });

        // Item da simulação de origem que este item repete (simulações de um ano da linha do tempo).
        Schema::table('simulacao_itens', function (Blueprint $table): void {
            $table->foreignId('origem_id')->nullable()->after('produto_id')->constrained('simulacao_itens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('simulacao_itens', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('origem_id');
        });

        Schema::table('simulacoes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('linha_do_tempo_id');
        });
    }
};
