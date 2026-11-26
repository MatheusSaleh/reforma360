<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comparação de uma simulação (regime regular de IBS/CBS) com o Simples Nacional,
     * para os mesmos itens. Uma por simulação (refazer substitui).
     */
    public function up(): void
    {
        Schema::create('comparacoes_regime', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('simulacao_id')->unique()->constrained('simulacoes')->cascadeOnDelete();
            $table->char('atividade', 2);
            $table->decimal('aliquota_cbs_simples', 7, 4);
            $table->decimal('aliquota_ibs_simples', 7, 4);
            $table->decimal('compras', 15, 2)->nullable();
            $table->unsignedInteger('itens_comparados')->default(0);
            $table->unsignedInteger('itens_nao_comparados')->default(0);
            $table->decimal('base_calculo', 15, 2)->default(0);
            $table->decimal('simples_cbs', 15, 2)->default(0);
            $table->decimal('simples_ibs', 15, 2)->default(0);
            $table->decimal('regular_cbs', 15, 2)->default(0);
            $table->decimal('regular_ibs', 15, 2)->default(0);
            $table->decimal('credito_estimado', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('comparacao_itens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comparacao_id')->constrained('comparacoes_regime')->cascadeOnDelete();
            $table->foreignId('simulacao_item_id')->constrained('simulacao_itens')->cascadeOnDelete();
            $table->string('estado', 30);
            $table->decimal('simples_cbs', 15, 2)->nullable();
            $table->decimal('simples_ibs', 15, 2)->nullable();
            $table->json('erros')->nullable();

            $table->index('comparacao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparacao_itens');
        Schema::dropIfExists('comparacoes_regime');
    }
};
