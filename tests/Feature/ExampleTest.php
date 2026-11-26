<?php

declare(strict_types=1);

test('a aplicação responde na rota inicial', function (): void {
    $this->get('/')->assertSuccessful();
});
