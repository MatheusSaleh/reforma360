<?php

declare(strict_types=1);

test('a rota inicial leva ao simulador', function (): void {
    $this->get('/')->assertRedirect('/simulacoes/item');
});
