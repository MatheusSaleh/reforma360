<?php

declare(strict_types=1);

namespace App\Services\Importacao;

use App\Models\Empresa;

/**
 * CST e cClassTrib de referência para itens que chegam sem eles (nota fiscal no
 * leiaute antigo): os de um produto da própria empresa com o mesmo NCM/NBS,
 * o atualizado mais recentemente. Memoriza por código durante a importação.
 */
final class ClassificacaoPeloCatalogo
{
    /** @var array<string, array{cst: string, c_class_trib: string}|null> */
    private array $porCodigo = [];

    public function __construct(
        private readonly Empresa $empresa,
    ) {}

    /**
     * @return array{cst: string, c_class_trib: string}|null
     */
    public function para(?string $ncm, ?string $nbs): ?array
    {
        [$coluna, $codigo] = $ncm !== null ? ['ncm', $ncm] : ['nbs', $nbs];

        if ($codigo === null) {
            return null;
        }

        $chave = "{$coluna}:{$codigo}";

        if (! array_key_exists($chave, $this->porCodigo)) {
            $produto = $this->empresa->produtos()->where($coluna, $codigo)->latest('updated_at')->latest('id')->first(['cst', 'c_class_trib']);

            $this->porCodigo[$chave] = $produto === null ? null : ['cst' => $produto->cst, 'c_class_trib' => $produto->c_class_trib];
        }

        return $this->porCodigo[$chave];
    }

    public function lembrar(?string $ncm, ?string $nbs, string $cst, string $cClassTrib): void
    {
        [$coluna, $codigo] = $ncm !== null ? ['ncm', $ncm] : ['nbs', $nbs];

        if ($codigo !== null) {
            $this->porCodigo["{$coluna}:{$codigo}"] = ['cst' => $cst, 'c_class_trib' => $cClassTrib];
        }
    }
}
