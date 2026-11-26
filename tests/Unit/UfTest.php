<?php

declare(strict_types=1);

use App\Enums\Uf;

test('tem as 27 UFs com código IBGE único', function (): void {
    $codigos = array_map(fn (Uf $uf): int => $uf->codigoIbge(), Uf::cases());

    expect(Uf::cases())->toHaveCount(27)
        ->and(array_unique($codigos))->toHaveCount(27);
});

test('verifica se o município pertence à UF pelo código IBGE', function (): void {
    expect(Uf::RS->contemMunicipio(4314902))->toBeTrue()
        ->and(Uf::SP->contemMunicipio(3550308))->toBeTrue()
        ->and(Uf::SP->contemMunicipio(4314902))->toBeFalse();
});
