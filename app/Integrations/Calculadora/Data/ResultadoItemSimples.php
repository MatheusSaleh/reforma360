<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use App\Enums\EstadoItemCalculo;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;

final readonly class ResultadoItemSimples
{
    use LeCamposResposta;

    /**
     * @param  numeric-string|null  $valorCbs
     * @param  numeric-string|null  $valorIbs
     * @param  numeric-string|null  $total
     * @param  list<ErroCalculo>  $erros
     */
    public function __construct(
        public int $numero,
        public EstadoItemCalculo $estado,
        public ?string $valorCbs = null,
        public ?string $valorIbs = null,
        public ?string $total = null,
        public array $erros = [],
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        $estado = EstadoItemCalculo::tryFrom(self::texto($dados, 'estadoItem'))
            ?? throw RespostaInesperadaException::campoInvalido('estadoItem');
        $tributos = self::grupoOpcional($dados, 'tribCalc');

        return new self(
            numero: self::inteiro($dados, 'nObj'),
            estado: $estado,
            valorCbs: $tributos === null ? null : self::decimal($tributos, 'vCBSSN'),
            valorIbs: $tributos === null ? null : self::decimal($tributos, 'vIBSSN'),
            total: $tributos === null ? null : self::decimal($tributos, 'totalItem'),
            erros: array_map(ErroCalculo::fromArray(...), self::listaDeGrupos($dados, 'erros')),
        );
    }

    public function foiCalculado(): bool
    {
        return $this->estado->foiCalculado() && $this->valorCbs !== null && $this->valorIbs !== null;
    }
}
