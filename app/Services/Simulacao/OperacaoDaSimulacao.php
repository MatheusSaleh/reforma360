<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Integrations\Calculadora\Data\ItemOperacao;
use App\Integrations\Calculadora\Data\Operacao;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use Illuminate\Support\Str;

/**
 * Monta a operação enviada à calculadora a partir dos parâmetros de uma simulação
 * e de uma lista de itens (numerados de 1 em diante, na ordem recebida).
 */
final class OperacaoDaSimulacao
{
    /**
     * @param  list<SimulacaoItem>  $itens
     */
    public static function montar(Simulacao $simulacao, array $itens): Operacao
    {
        $aliquotas = $simulacao->aliquotasNominais();

        return new Operacao(
            id: 'simulacao-'.$simulacao->id.'-'.Str::lower(Str::random(8)),
            dhFatoGerador: $simulacao->data_fato_gerador->shiftTimezone('America/Sao_Paulo')->startOfDay(),
            municipio: $simulacao->municipio,
            uf: $simulacao->uf->value,
            itens: array_map(fn (int $indice, SimulacaoItem $item): ItemOperacao => new ItemOperacao(
                numero: $indice + 1,
                cst: $item->cst,
                cClassTrib: $item->c_class_trib,
                baseCalculo: $item->base_calculo,
                ncm: $item->ncm,
                nbs: $item->nbs,
                aliquotasNominais: $aliquotas,
            ), array_keys($itens), $itens),
        );
    }
}
