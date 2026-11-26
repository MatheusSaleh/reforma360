<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Enums\EstadoItemCalculo;
use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\XmlTributacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Models\Simulacao;
use App\Services\Simulacao\Data\XmlDoGrupo;
use Illuminate\Container\Attributes\Config;
use Illuminate\Validation\ValidationException;

/**
 * Gera, pela calculadora, o XML do grupo IBS/CBS dos itens calculados de uma simulação
 * e só o entrega depois de validado contra o XSD oficial do documento.
 *
 * A calculadora gera o grupo apenas para NF-e e NFC-e.
 */
final readonly class GerarXmlDoGrupoService
{
    public function __construct(
        private XmlTributacao $xml,
        #[Config('simulacao.maximo_itens_xml')]
        private int $maximoItens = 990,
    ) {}

    /**
     * Tipo de documento do XML, ou o motivo de não ser possível gerar.
     *
     * @return array{tipo: TipoDocumentoFiscal|null, motivo: string|null}
     */
    public function disponibilidade(Simulacao $simulacao): array
    {
        $tipoDaNota = $simulacao->importacao?->tipo_documento;

        if ($tipoDaNota !== null && ! $tipoDaNota->geraXmlDoGrupo()) {
            return ['tipo' => null, 'motivo' => 'A calculadora da Receita ainda não gera o XML do grupo IBS/CBS para NFS-e.'];
        }

        if ($tipoDaNota === null && $simulacao->itens()->whereNotNull('nbs')->exists()) {
            return ['tipo' => null, 'motivo' => 'A simulação tem serviços (NBS): a calculadora da Receita gera o XML do grupo IBS/CBS só para NF-e e NFC-e.'];
        }

        return ['tipo' => $tipoDaNota ?? TipoDocumentoFiscal::Nfe, 'motivo' => null];
    }

    /**
     * @throws ValidationException quando não é possível gerar ou o XML não passa no XSD
     * @throws CalculadoraIndisponivelException
     */
    public function gerar(Simulacao $simulacao): XmlDoGrupo
    {
        if ($simulacao->status->emAndamento()) {
            throw self::erro('Aguarde a simulação terminar para gerar o XML.');
        }

        ['tipo' => $tipo, 'motivo' => $motivo] = $this->disponibilidade($simulacao);

        if ($tipo === null) {
            throw self::erro((string) $motivo);
        }

        $itens = array_values($simulacao->itens()
            ->whereIn('estado', [EstadoItemCalculo::Calculado, EstadoItemCalculo::CalculoSimulado])
            ->orderBy('id')
            ->get()
            ->all());

        if ($itens === []) {
            throw self::erro('A simulação não tem itens calculados para gerar o XML.');
        }

        if (count($itens) > $this->maximoItens) {
            throw self::erro("Uma {$tipo->label()} aceita até {$this->maximoItens} itens; a simulação tem ".count($itens).' itens calculados.');
        }

        try {
            $conteudo = $this->xml->gerarGrupo(OperacaoDaSimulacao::montar($simulacao, $itens), $tipo);
        } catch (PayloadInvalidoException $e) {
            throw self::erro('A calculadora recusou gerar o XML: '.$e->getMessage());
        }

        $validacao = $this->xml->validar($conteudo, $tipo);

        if (! $validacao->valido) {
            throw self::erro('O XML gerado não passou na validação do XSD da Receita: '.$validacao->mensagem);
        }

        return new XmlDoGrupo(
            conteudo: $conteudo,
            tipo: $tipo,
            quantidadeItens: count($itens),
            nomeArquivo: "simulacao-{$simulacao->id}-grupo-ibscbs-{$tipo->value}.xml",
        );
    }

    private static function erro(string $mensagem): ValidationException
    {
        return ValidationException::withMessages(['xml' => $mensagem]);
    }
}
