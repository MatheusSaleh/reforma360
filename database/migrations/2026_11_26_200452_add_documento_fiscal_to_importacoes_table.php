<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dados do documento fiscal quando a importação vem de um XML (NF-e, NFC-e, NFS-e).
     */
    public function up(): void
    {
        Schema::table('importacoes', function (Blueprint $table): void {
            $table->string('tipo_documento', 10)->nullable()->after('formato');
            $table->string('numero_documento', 20)->nullable()->after('tipo_documento');
            $table->date('data_documento')->nullable()->after('numero_documento');
            $table->char('uf', 2)->nullable()->after('data_documento');
            $table->unsignedInteger('municipio')->nullable()->after('uf');
        });
    }

    public function down(): void
    {
        Schema::table('importacoes', function (Blueprint $table): void {
            $table->dropColumn(['tipo_documento', 'numero_documento', 'data_documento', 'uf', 'municipio']);
        });
    }
};
