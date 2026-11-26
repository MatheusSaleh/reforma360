<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use App\Enums\EstadoItemCalculo;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;

/**
 * Resultado de um item. Na observabilidade, cada item tem seu próprio estado:
 * itens com erro trazem a lista de erros e nenhum tributo calculado.
 */
final readonly class ResultadoItem
{
    use LeCamposResposta;

    /**
     * @param  list<ErroCalculo>  $erros
     */
    public function __construct(
        public int $numero,
        public EstadoItemCalculo $estado,
        public ?TributacaoIbsCbs $ibsCbs = null,
        public ?ImpostoSeletivoCalculado $impostoSeletivo = null,
        public array $erros = [],
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        $estado = EstadoItemCalculo::tryFrom(self::texto($dados, 'estadoItem'))
            ?? throw RespostaInesperadaException::campoInvalido('estadoItem');

        $tributos = self::grupoOpcional($dados, 'tribCalc') ?? [];
        $ibsCbs = self::grupoOpcional($tributos, 'IBSCBS');
        $impostoSeletivo = self::grupoOpcional($tributos, 'IS');

        return new self(
            numero: self::inteiro($dados, 'nObj'),
            estado: $estado,
            ibsCbs: $ibsCbs === null ? null : TributacaoIbsCbs::fromArray($ibsCbs),
            impostoSeletivo: $impostoSeletivo === null ? null : ImpostoSeletivoCalculado::fromArray($impostoSeletivo),
            erros: array_map(ErroCalculo::fromArray(...), self::listaDeGrupos($dados, 'erros')),
        );
    }

    public function foiCalculado(): bool
    {
        return $this->estado->foiCalculado();
    }

    public function temErros(): bool
    {
        return $this->erros !== [];
    }
}
