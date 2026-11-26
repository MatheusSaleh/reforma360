<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Atividade do Simples Nacional (cAtivSN) vigente, do catálogo da calculadora.
 */
final readonly class AtividadeSimples
{
    use LeCamposResposta;

    /**
     * @param  list<string>  $tiposDocumento
     */
    public function __construct(
        public string $codigo,
        public string $descricao,
        public string $anexo,
        public array $tiposDocumento,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        $tipos = $dados['tpDocsAdmissiveis'] ?? [];

        return new self(
            codigo: self::texto($dados, 'codigo'),
            descricao: self::texto($dados, 'descricao'),
            anexo: self::texto($dados, 'rotuloAnexo'),
            tiposDocumento: is_array($tipos) ? array_values(array_filter($tipos, is_string(...))) : [],
        );
    }
}
