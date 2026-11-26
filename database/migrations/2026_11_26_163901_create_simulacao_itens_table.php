<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada item guarda um retrato do produto no momento da simulação (o produto pode
     * mudar ou ser excluído depois) e o resultado da calculadora.
     */
    public function up(): void
    {
        Schema::create('simulacao_itens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('simulacao_id')->constrained('simulacoes')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained()->nullOnDelete();
            $table->string('descricao');
            $table->char('ncm', 8)->nullable();
            $table->char('nbs', 9)->nullable();
            $table->char('cst', 3);
            $table->char('c_class_trib', 6);
            $table->decimal('base_calculo', 15, 2);
            $table->string('estado', 30)->nullable();
            $table->decimal('valor_cbs', 15, 2)->nullable();
            $table->decimal('valor_ibs', 15, 2)->nullable();
            $table->decimal('valor_imposto_seletivo', 15, 2)->nullable();
            $table->decimal('total_tributos', 15, 2)->nullable();
            $table->decimal('percentual_reducao', 7, 4)->nullable();
            $table->json('detalhes')->nullable();
            $table->json('erros')->nullable();
            $table->timestamps();

            $table->index(['simulacao_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulacao_itens');
    }
};
