<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importacao_erros', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('importacao_id')->constrained('importacoes')->cascadeOnDelete();
            $table->unsignedInteger('linha');
            $table->string('campo', 50)->nullable();
            $table->string('mensagem', 500);

            $table->index(['importacao_id', 'linha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacao_erros');
    }
};
