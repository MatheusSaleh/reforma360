<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pendente');
            $table->date('data_fato_gerador');
            $table->char('uf', 2);
            $table->unsignedInteger('municipio');
            $table->decimal('aliquota_cbs', 7, 4)->nullable();
            $table->decimal('aliquota_ibs_estadual', 7, 4)->nullable();
            $table->decimal('aliquota_ibs_municipal', 7, 4)->nullable();
            $table->unsignedInteger('total_itens')->default(0);
            $table->unsignedInteger('itens_processados')->default(0);
            $table->unsignedInteger('itens_com_erro')->default(0);
            $table->decimal('total_base_calculo', 15, 2)->nullable();
            $table->decimal('total_cbs', 15, 2)->nullable();
            $table->decimal('total_ibs', 15, 2)->nullable();
            $table->decimal('total_imposto_seletivo', 15, 2)->nullable();
            $table->decimal('total_tributos', 15, 2)->nullable();
            $table->string('batch_id', 36)->nullable();
            $table->timestamp('iniciada_em')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulacoes');
    }
};
