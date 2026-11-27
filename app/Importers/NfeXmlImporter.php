<?php

declare(strict_types=1);

namespace App\Importers;

use App\Enums\FormatoImportacao;
use App\Enums\TipoDocumentoFiscal;
use App\Enums\Uf;
use App\Importers\Data\DocumentoFiscal;
use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
use App\Support\NormalizadorFiscal;
use Carbon\CarbonImmutable;
use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * XML de NF-e/NFC-e (leiaute 4.00, com ou sem o grupo IBSCBS) e de NFS-e nacional.
 *
 * Cada item da nota vira uma linha: descrição, NCM/NBS, preço unitário (catálogo),
 * valor do item (base de cálculo da nota) e, quando a nota já traz o grupo IBS/CBS,
 * CST e cClassTrib.
 *
 * Segurança: XML com DOCTYPE/ENTITY é recusado (nota fiscal não tem; é o vetor de
 * XXE e "billion laughs") e o parse usa LIBXML_NONET, sem expandir entidades.
 */
final readonly class NfeXmlImporter implements ImportadorDeDocumentoFiscal
{
    private const string NS_NFE = 'http://www.portalfiscal.inf.br/nfe';

    private const string NS_NFSE = 'http://www.sped.fazenda.gov.br/nfse';

    public function __construct(
        #[Config('importacao.tamanho_lote')]
        private int $tamanhoLote = 500,
    ) {}

    public function suporta(FormatoImportacao $formato): bool
    {
        return $formato === FormatoImportacao::Xml;
    }

    public function ler(string $caminho, Closure $aoLerLote): void
    {
        [$xpath, $nota, $tipo] = $this->nota($caminho);

        $linhas = $tipo === TipoDocumentoFiscal::Nfse
            ? [$this->linhaDaNfse($xpath, $nota)]
            : $this->linhasDaNfe($xpath, $nota);

        if ($linhas === []) {
            throw ArquivoInvalidoException::semLinhas();
        }

        foreach (array_chunk($linhas, max(1, $this->tamanhoLote)) as $lote) {
            $aoLerLote($lote);
        }
    }

    public function documento(string $caminho): DocumentoFiscal
    {
        [$xpath, $nota, $tipo] = $this->nota($caminho);

        if ($tipo === TipoDocumentoFiscal::Nfse) {
            $municipio = self::inteiro($this->texto($xpath, 'nfse:cLocIncid', $nota)
                ?? $this->texto($xpath, './/nfse:serv/nfse:locPrest/nfse:cLocPrestacao', $nota));

            return new DocumentoFiscal(
                tipo: $tipo,
                numero: $this->obrigatorio($xpath, 'nfse:nNFSe', $nota),
                data: self::data($this->obrigatorio($xpath, './/nfse:infDPS/nfse:dhEmi', $nota)),
                uf: $municipio === null ? null : Uf::doMunicipio($municipio),
                municipio: $municipio,
            );
        }

        $municipio = self::inteiro($this->texto($xpath, 'nfe:ide/nfe:cMunFG', $nota));

        return new DocumentoFiscal(
            tipo: $tipo,
            numero: $this->obrigatorio($xpath, 'nfe:ide/nfe:nNF', $nota),
            data: self::data($this->obrigatorio($xpath, 'nfe:ide/nfe:dhEmi', $nota)),
            uf: $municipio === null ? null : Uf::doMunicipio($municipio),
            municipio: $municipio,
        );
    }

    /**
     * @return list<LinhaPlanilha>
     */
    private function linhasDaNfe(DOMXPath $xpath, DOMElement $nota): array
    {
        $linhas = [];

        foreach ($xpath->query('nfe:det', $nota) ?: [] as $indice => $item) {
            if (! $item instanceof DOMElement) {
                continue;
            }

            $valorUnitario = $this->texto($xpath, 'nfe:prod/nfe:vUnCom', $item);

            $linhas[] = new LinhaPlanilha(
                linha: (int) ($item->getAttribute('nItem') ?: $indice + 1),
                campos: [
                    'descricao'       => $this->texto($xpath, 'nfe:prod/nfe:xProd', $item),
                    'ncm'             => $this->texto($xpath, 'nfe:prod/nfe:NCM', $item),
                    'nbs'             => null,
                    'cst'             => $this->texto($xpath, 'nfe:imposto/nfe:IBSCBS/nfe:CST', $item),
                    'c_class_trib'    => $this->texto($xpath, 'nfe:imposto/nfe:IBSCBS/nfe:cClassTrib', $item),
                    'preco'           => $valorUnitario !== null && is_numeric($valorUnitario) ? NormalizadorFiscal::arredondar($valorUnitario, 2) : $valorUnitario,
                    'valor_documento' => $this->texto($xpath, 'nfe:prod/nfe:vProd', $item),
                    ...$this->impostoSeletivoDaNfe($xpath, $item),
                ],
            );
        }

        return $linhas;
    }

    /**
     * @return array<string, string|null>
     */
    private function impostoSeletivoDaNfe(DOMXPath $xpath, DOMElement $item): array
    {
        $grupos = $xpath->query('nfe:imposto/nfe:IS', $item);
        $grupo = $grupos === false ? null : $grupos->item(0);

        if (! $grupo instanceof DOMElement) {
            return [];
        }

        $quantidade = $this->texto($xpath, 'nfe:qTrib', $grupo);
        $quantidadeComercial = $this->texto($xpath, 'nfe:prod/nfe:qCom', $item);
        $decimal = '/^\d{1,11}(?:\.\d{1,4})?$/D';
        $quantidadeUnitaria = null;

        if (is_numeric($quantidade) && is_numeric($quantidadeComercial)
            && preg_match($decimal, $quantidade) === 1 && preg_match($decimal, $quantidadeComercial) === 1
            && bccomp($quantidadeComercial, '0', 4) > 0) {
            $quantidadeUnitaria = NormalizadorFiscal::arredondar(bcdiv($quantidade, $quantidadeComercial, 5), 4);
        }

        return [
            'is_cst'                  => $this->texto($xpath, 'nfe:CSTIS', $grupo),
            'is_c_class_trib'         => $this->texto($xpath, 'nfe:cClassTribIS', $grupo),
            'is_unidade'              => $this->texto($xpath, 'nfe:uTrib', $grupo),
            'is_quantidade'           => $quantidadeUnitaria,
            'is_quantidade_documento' => $quantidade,
            'is_aliquota_ad_valorem'  => $this->texto($xpath, 'nfe:pIS', $grupo),
            'is_aliquota_ad_rem'      => $this->texto($xpath, 'nfe:pISEspec', $grupo),
        ];
    }

    private function linhaDaNfse(DOMXPath $xpath, DOMElement $nota): LinhaPlanilha
    {
        $valor = $this->texto($xpath, './/nfse:infDPS/nfse:valores/nfse:vServPrest/nfse:vServ', $nota);
        $tributacao = './/nfse:infDPS/nfse:IBSCBS/nfse:valores/nfse:trib/nfse:gIBSCBS';

        return new LinhaPlanilha(1, [
            'descricao' => $this->texto($xpath, './/nfse:infDPS/nfse:serv/nfse:cServ/nfse:xDescServ', $nota)
                ?? $this->texto($xpath, 'nfse:xNBS', $nota),
            'ncm'             => null,
            'nbs'             => $this->texto($xpath, './/nfse:infDPS/nfse:serv/nfse:cServ/nfse:cNBS', $nota),
            'cst'             => $this->texto($xpath, $tributacao.'/nfse:CST', $nota),
            'c_class_trib'    => $this->texto($xpath, $tributacao.'/nfse:cClassTrib', $nota),
            'preco'           => $valor,
            'valor_documento' => $valor,
        ]);
    }

    /**
     * @return array{0: DOMXPath, 1: DOMElement, 2: TipoDocumentoFiscal}
     */
    private function nota(string $caminho): array
    {
        $xpath = new DOMXPath($this->carregar($caminho));
        $xpath->registerNamespace('nfe', self::NS_NFE);
        $xpath->registerNamespace('nfse', self::NS_NFSE);

        foreach (['//nfe:NFe/nfe:infNFe', '//nfse:NFSe/nfse:infNFSe'] as $expressao) {
            $encontradas = $xpath->query($expressao);
            $quantidade = $encontradas === false ? 0 : $encontradas->length;

            if ($quantidade > 1) {
                throw ArquivoInvalidoException::maisDeUmaNota($quantidade);
            }

            $nota = $encontradas === false ? null : $encontradas->item(0);

            if ($nota instanceof DOMElement) {
                return [$xpath, $nota, $this->tipo($xpath, $nota)];
            }
        }

        throw ArquivoInvalidoException::naoEhNotaFiscal();
    }

    private function tipo(DOMXPath $xpath, DOMElement $nota): TipoDocumentoFiscal
    {
        if ($nota->namespaceURI === self::NS_NFSE) {
            return TipoDocumentoFiscal::Nfse;
        }

        return $this->texto($xpath, 'nfe:ide/nfe:mod', $nota) === '65' ? TipoDocumentoFiscal::Nfce : TipoDocumentoFiscal::Nfe;
    }

    private function carregar(string $caminho): DOMDocument
    {
        $conteudo = (string) Storage::disk('local')->get($caminho);

        if (preg_match('/<!(DOCTYPE|ENTITY)/i', $conteudo) === 1) {
            throw ArquivoInvalidoException::xmlInseguro();
        }

        $usoAnterior = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $documento = new DOMDocument;
            $carregou = $conteudo !== '' && $documento->loadXML($conteudo, LIBXML_NONET | LIBXML_COMPACT);
            $erro = libxml_get_last_error();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($usoAnterior);
        }

        if (! $carregou) {
            throw ArquivoInvalidoException::xmlMalformado($erro === false ? 'arquivo vazio.' : mb_trim($erro->message)." (linha {$erro->line}).");
        }

        return $documento;
    }

    private function texto(DOMXPath $xpath, string $expressao, DOMElement $contexto): ?string
    {
        $resultado = $xpath->query($expressao, $contexto);
        $no = $resultado === false ? null : $resultado->item(0);
        $texto = $no instanceof DOMNode ? mb_trim($no->textContent) : '';

        return $texto === '' ? null : $texto;
    }

    private function obrigatorio(DOMXPath $xpath, string $expressao, DOMElement $contexto): string
    {
        return $this->texto($xpath, $expressao, $contexto)
            ?? throw ArquivoInvalidoException::campoObrigatorioDaNota(basename(str_replace(['nfe:', 'nfse:'], '', $expressao)));
    }

    private static function inteiro(?string $valor): ?int
    {
        return $valor !== null && ctype_digit($valor) ? (int) $valor : null;
    }

    /**
     * Data de emissão como está na nota (horário local do emissor).
     */
    private static function data(string $valor): CarbonImmutable
    {
        try {
            return CarbonImmutable::parse(mb_substr($valor, 0, 10), 'America/Sao_Paulo')->startOfDay();
        } catch (Throwable) {
            throw ArquivoInvalidoException::campoObrigatorioDaNota('dhEmi');
        }
    }
}
