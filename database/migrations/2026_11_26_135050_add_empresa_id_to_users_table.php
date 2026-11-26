<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Usuários que já existirem ganham uma empresa própria antes de a coluna virar obrigatória.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('empresa_id')->nullable()->after('id');
        });

        DB::table('users')->whereNull('empresa_id')->orderBy('id')->each(function (object $usuario): void {
            $empresaId = DB::table('empresas')->insertGetId([
                'razao_social' => "Empresa de {$usuario->name}",
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::table('users')->where('id', $usuario->id)->update(['empresa_id' => $empresaId]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('empresa_id')->nullable(false)->change();
            $table->foreign('empresa_id')->references('id')->on('empresas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('empresa_id');
        });
    }
};
