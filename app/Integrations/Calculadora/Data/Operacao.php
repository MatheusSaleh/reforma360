<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Operação (documento) enviada à calculadora do regime regular.
 */
final readonly class Operacao
{
    /**
     * @param  list<ItemOperacao>  $itens
     */
    public function __construct(
        public string $id,
        public CarbonImmutable $dhFatoGerador,
        public int $municipio,
        public string $uf,
        public array $itens,
        public string $versao = '1.0.0',
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
            'id'            => $this->id,
            'versao'        => $this->versao,
            'dhFatoGerador' => $this->dhFatoGerador->toIso8601String(),
            'municipio'     => $this->municipio,
            'uf'            => $this->uf,
            'itens'         => array_map(fn (ItemOperacao $item): array => $item->toPayload(), $this->itens),
        ];
    }
}
