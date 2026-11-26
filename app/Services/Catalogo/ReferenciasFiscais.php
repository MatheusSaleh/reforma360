<?php

declare(strict_types=1);

namespace App\Services\Catalogo;

use App\Enums\TipoNomenclatura;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\ItemNomenclatura;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use Carbon\CarbonImmutable;

/**
 * Confere códigos fiscais contra os dados abertos vigentes na data.
 * Os conjuntos são montados uma vez por instância (uma importação = milhares de consultas).
 */
final class ReferenciasFiscais
{
    /** @var array<string, array<string, true>> */
    private array $nomenclaturas = [];

    /** @var array<string, array<string, true>>|null cst => [cClassTrib => true] */
    private ?array $classificacoesPorCst = null;

    public function __construct(
        private readonly DadosAbertos $dadosAbertos,
        private readonly CarbonImmutable $data,
    ) {}

    /**
     * @throws CalculadoraIndisponivelException
     */
    public function existeNomenclatura(TipoNomenclatura $tipo, string $codigo): bool
    {
        $this->nomenclaturas[$tipo->value] ??= array_fill_keys(
            array_map(fn (ItemNomenclatura $item): string => $item->codigo, $this->dadosAbertos->nomenclaturas($tipo, $this->data)),
            true,
        );

        return isset($this->nomenclaturas[$tipo->value][$codigo]);
    }

    /**
     * Mensagem de erro quando o CST não existe ou o cClassTrib não pertence a ele.
     *
     * @return array{campo: string, mensagem: string}|null
     *
     * @throws CalculadoraIndisponivelException
     */
    public function erroDeClassificacao(string $cst, string $cClassTrib): ?array
    {
        if ($this->classificacoesPorCst === null) {
            $this->classificacoesPorCst = [];

            foreach ($this->dadosAbertos->situacoesTributarias($this->data) as $situacao) {
                $this->classificacoesPorCst[$situacao->codigo] = array_fill_keys(array_column($situacao->classificacoes, 'codigo'), true);
            }
        }

        if (! isset($this->classificacoesPorCst[$cst])) {
            return ['campo' => 'cst', 'mensagem' => "CST {$cst} não existe na tabela vigente."];
        }

        if (! isset($this->classificacoesPorCst[$cst][$cClassTrib])) {
            return ['campo' => 'c_class_trib', 'mensagem' => "cClassTrib {$cClassTrib} não pertence ao CST {$cst}."];
        }

        return null;
    }
}
