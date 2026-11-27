<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dados do grupo IS da linha da nota, com a quantidade tributável total.
     */
    public function up(): void
    {
        Schema::table('importacao_produtos', function (Blueprint $table): void {
            $table->json('dados_imposto_seletivo')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('importacao_produtos', function (Blueprint $table): void {
            $table->dropColumn('dados_imposto_seletivo');
        });
    }
};
