<?php

declare(strict_types=1);

namespace App\Services\Catalogo;

use App\Enums\TipoNomenclatura;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Data\ItemNomenclatura;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Busca de NCM/NBS para autocomplete: por prefixo do código (com ou sem pontos)
 * ou por palavras da descrição, sem diferenciar acentos e maiúsculas.
 */
final readonly class BuscaNomenclaturaService
{
    private const int CACHE_SEGUNDOS = 86400;

    public function __construct(
        private DadosAbertos $dadosAbertos,
    ) {}

    /**
     * @return list<ItemNomenclatura>
     */
    public function buscar(TipoNomenclatura $tipo, string $termo, CarbonImmutable $data, int $limite = 20): array
    {
        $termo = mb_trim($termo);
        $digitos = (string) preg_replace('/\D/', '', $termo);
        $porCodigo = $digitos !== '' && preg_match('/^[\d.\-\s]+$/', $termo) === 1;
        $palavras = $porCodigo ? [] : self::palavras($termo);

        if (! $porCodigo && $palavras === []) {
            return [];
        }

        $encontrados = [];

        foreach ($this->indice($tipo, $data) as [$codigo, $descricao, $normalizada]) {
            if ($porCodigo ? str_starts_with($codigo, $digitos) : self::contemTodas($normalizada, $palavras)) {
                $encontrados[] = new ItemNomenclatura($codigo, $descricao);

                if (count($encontrados) === $limite) {
                    break;
                }
            }
        }

        return $encontrados;
    }

    /**
     * Índice [código, descrição, descrição normalizada], em cache: normalizar
     * ~10 mil NCMs a cada tecla custaria dezenas de milissegundos.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function indice(TipoNomenclatura $tipo, CarbonImmutable $data): array
    {
        return Cache::remember(
            "catalogo:indice-nomenclatura:{$tipo->value}:{$data->toDateString()}",
            self::CACHE_SEGUNDOS,
            fn (): array => array_map(
                fn (ItemNomenclatura $item): array => [$item->codigo, $item->descricao, self::normalizar($item->descricao)],
                $this->dadosAbertos->nomenclaturas($tipo, $data),
            ),
        );
    }

    /**
     * @return list<string>
     */
    private static function palavras(string $termo): array
    {
        return array_values(array_filter(
            explode(' ', self::normalizar($termo)),
            fn (string $palavra): bool => mb_strlen($palavra) >= 2,
        ));
    }

    /**
     * @param  list<string>  $palavras
     */
    private static function contemTodas(string $texto, array $palavras): bool
    {
        foreach ($palavras as $palavra) {
            if (! str_contains($texto, $palavra)) {
                return false;
            }
        }

        return true;
    }

    private static function normalizar(string $texto): string
    {
        return (string) preg_replace('/\s+/', ' ', Str::lower(Str::ascii($texto)));
    }
}
