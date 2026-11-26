<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('descricao');
            $table->char('ncm', 8)->nullable();
            $table->char('nbs', 9)->nullable();
            $table->char('cst', 3);
            $table->char('c_class_trib', 6);
            $table->decimal('preco', 15, 2);
            $table->timestamps();

            $table->index(['empresa_id', 'descricao']);
            $table->index(['empresa_id', 'ncm']);
            $table->index(['empresa_id', 'nbs']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
