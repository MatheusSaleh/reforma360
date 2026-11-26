<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Operação do Simples Nacional (CRT 1). A calculadora só aceita fato gerador a partir de 2027.
 */
final readonly class OperacaoSimples
{
    /**
     * @param  list<ItemSimples>  $itens
     * @param  string  $tipoDocumento  55 (NF-e) ou 65 (NFC-e)
     */
    public function __construct(
        public string $id,
        public CarbonImmutable $data,
        public array $itens,
        public string $tipoDocumento = '55',
    ) {
        if ($itens === []) {
            throw new InvalidArgumentException('A operação precisa de pelo menos um item.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id'     => $this->id,
            'versao' => '2.0.0',
            'data'   => $this->data->toDateString(),
            'tpDoc'  => $this->tipoDocumento,
            'CRT'    => '1',
            'itens'  => array_map(fn (ItemSimples $item): array => $item->toPayload(), $this->itens),
        ];
    }
}
