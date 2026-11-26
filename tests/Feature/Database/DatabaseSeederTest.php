<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('o seeder de demonstração é idempotente', function (): void {
    $this->seed();
    $this->seed();

    expect(Empresa::query()->count())->toBe(1)
        ->and(User::query()->where('email', 'demo@reforma360.test')->count())->toBe(1)
        ->and(Produto::query()->count())->toBe(4);
});
