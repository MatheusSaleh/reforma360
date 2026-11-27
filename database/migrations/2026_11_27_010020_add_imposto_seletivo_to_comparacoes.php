<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comparacoes', function (Blueprint $table): void {
            Schema::table('comparacoes_regime', function (Blueprint $table): void {
                $table->decimal('valor_imposto_seletivo', 15, 2)->default('0.00');
            });

            Schema::table('comparacao_itens', function (Blueprint $table): void {
                $table->decimal('valor_imposto_seletivo', 15, 2)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comparacoes', function (Blueprint $table): void {
            Schema::table('comparacao_itens', function (Blueprint $table): void {
                $table->dropColumn('valor_imposto_seletivo');
            });

            Schema::table('comparacoes_regime', function (Blueprint $table): void {
                $table->dropColumn('valor_imposto_seletivo');
            });
        });
    }
};
