<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dados do Imposto Seletivo (a partir de 2027, só para NCMs tributados). As alíquotas ainda
     * não estão em lei: são estimativas do usuário. Ad valorem em %; ad rem em R$ por unidade.
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table): void {
            $table->char('is_cst', 3)->nullable()->after('preco');
            $table->char('is_c_class_trib', 6)->nullable()->after('is_cst');
            $table->string('is_unidade', 6)->nullable()->after('is_c_class_trib');
            $table->decimal('is_quantidade', 15, 4)->nullable()->after('is_unidade');
            $table->decimal('is_aliquota_ad_valorem', 7, 4)->nullable()->after('is_quantidade');
            $table->decimal('is_aliquota_ad_rem', 15, 4)->nullable()->after('is_aliquota_ad_valorem');
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table): void {
            $table->dropColumn(['is_cst', 'is_c_class_trib', 'is_unidade', 'is_quantidade', 'is_aliquota_ad_valorem', 'is_aliquota_ad_rem']);
        });
    }
};
