<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Itens importados: qual produto cada linha gravou e com que valor. Na nota fiscal o
     * valor é o do item (base de cálculo da nota), que pode diferir do preço do catálogo.
     */
    public function up(): void
    {
        Schema::create('importacao_produtos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('importacao_id')->constrained('importacoes')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('linha');
            $table->decimal('valor', 15, 2);

            $table->index(['importacao_id', 'linha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacao_produtos');
    }
};
