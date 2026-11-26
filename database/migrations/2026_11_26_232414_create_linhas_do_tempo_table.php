<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Linha do tempo 2026→2033 de uma simulação: uma simulação filha por ano,
     * com as alíquotas do cronograma de transição sobre as alíquotas plenas estimadas.
     */
    public function up(): void
    {
        Schema::create('linhas_do_tempo', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('simulacao_id')->unique()->constrained('simulacoes')->cascadeOnDelete();
            $table->string('status', 30)->default('processando');
            $table->decimal('aliquota_plena_cbs', 7, 4);
            $table->decimal('aliquota_plena_ibs_estadual', 7, 4);
            $table->decimal('aliquota_plena_ibs_municipal', 7, 4);
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linhas_do_tempo');
    }
};
