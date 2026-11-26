<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use App\Enums\Uf;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Integrations\Calculadora\Data\Municipio;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Dados abertos da calculadora do regime regular, com cache: as tabelas só mudam
 * quando a base de dados da calculadora é atualizada.
 */
final readonly class DadosAbertosClient implements DadosAbertos
{
    private const string BASE = 'calculadora/dados-abertos';

    private const int CACHE_SEGUNDOS = 86400;

    public function __construct(
        private CalculadoraRegularHttp $http,
    ) {}

    public function municipios(Uf $uf): array
    {
        $lista = Cache::remember(
            "calculadora:municipios:{$uf->value}",
            self::CACHE_SEGUNDOS,
            fn (): array => $this->lista($this->get(self::BASE.'/ufs/municipios', ['siglaUf' => $uf->value])),
        );

        return array_map(Municipio::fromArray(...), $lista);
    }

    public function classificacaoTributaria(string $cClassTrib, CarbonImmutable $data): ?ClassificacaoTributaria
    {
        $dados = Cache::remember(
            "calculadora:class-trib:{$cClassTrib}:{$data->toDateString()}",
            self::CACHE_SEGUNDOS,
            fn (): array => $this->objetoOuVazio(
                $this->get(self::BASE."/classificacoes-tributarias/cbs-ibs/class-trib/{$cClassTrib}", ['data' => $data->toDateString()]),
            ),
        );

        return $dados === [] ? null : ClassificacaoTributaria::fromArray($dados);
    }

    public function fundamentacaoLegal(string $cClassTrib, CarbonImmutable $data): ?FundamentacaoLegal
    {
        $lista = Cache::remember(
            "calculadora:fundamentacoes:{$data->toDateString()}",
            self::CACHE_SEGUNDOS,
            fn (): array => $this->lista($this->get(self::BASE.'/fundamentacoes-legais', ['data' => $data->toDateString()])),
        );

        foreach ($lista as $fundamentacao) {
            if (($fundamentacao['codigoClassificacaoTributaria'] ?? null) === $cClassTrib) {
                return FundamentacaoLegal::fromArray($fundamentacao);
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $query
     */
    private function get(string $caminho, array $query): Response
    {
        try {
            $response = $this->http->request()->get($caminho, $query);
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::semConexao(null, $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::erroDoServidor(null, $response->status());
        }

        return $response;
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function lista(Response $response): array
    {
        $corpo = $response->throw()->json();

        if (! is_array($corpo) || ! array_is_list($corpo)) {
            throw RespostaInesperadaException::corpoInvalido();
        }

        return array_values(array_filter($corpo, is_array(...)));
    }

    /**
     * Objeto da resposta, ou [] quando o registro não existe (404). O vazio também vai
     * para o cache, para não consultar de novo um código inexistente.
     *
     * @return array<array-key, mixed>
     */
    private function objetoOuVazio(Response $response): array
    {
        if ($response->notFound()) {
            return [];
        }

        $corpo = $response->throw()->json();

        if (! is_array($corpo)) {
            throw RespostaInesperadaException::corpoInvalido();
        }

        return $corpo;
    }
}
