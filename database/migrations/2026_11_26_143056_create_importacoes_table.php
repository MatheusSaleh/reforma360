<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nome_arquivo');
            $table->string('caminho_arquivo')->nullable();
            $table->string('formato', 10);
            $table->string('status', 20)->default('pendente');
            $table->unsignedInteger('total_linhas')->default(0);
            $table->unsignedInteger('linhas_importadas')->default(0);
            $table->unsignedInteger('linhas_com_erro')->default(0);
            $table->string('mensagem_erro', 500)->nullable();
            $table->timestamp('iniciada_em')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacoes');
    }
};
