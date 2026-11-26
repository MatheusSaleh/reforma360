---
name: pest-testing
description: "Use para testes Pest no Reforma360. Ative sempre que um teste for escrito, editado, corrigido ou refatorado — inclusive testes que quebraram após mudança de código, asserts, datasets e TDD. Ative quando o usuário perguntar como escrever algo em Pest ou mencionar tests/Feature ou tests/Unit."
---

# Pest Testing (Reforma360)

## Criar

```bash
php artisan make:test --pest {Nome}         # feature
php artisan make:test --pest --unit {Nome}  # unit
```

Não coloque `Feature/` ou `Unit/` no `{Nome}`. Organize por domínio: `tests/Feature/Simulacao/`, `tests/Feature/Importacao/`, `tests/Feature/Api/V1/`.

## Rodar

```bash
php artisan test --compact --filter=nomeDoTeste
php artisan test --compact tests/Feature/Simulacao/SimularItemTest.php
./vendor/bin/pest                       # suite completa (CI)
```

Rode o menor conjunto que cobre a mudança. Não apague testes sem aprovação.

## Estilo

- `test('descrição em PT-BR', fn () => ...)` (siga os irmãos se já usarem `it()`).
- `expect()` encadeado; asserts HTTP específicos (`assertSuccessful`, `assertForbidden`, `assertRedirect(route(...))`, `assertSessionHasErrors`, `assertJsonPath`).
- Datasets para tabelas de cálculo:

```php
test('arredonda CBS no centavo', function (string $base, string $aliquota, string $esperado) {
    expect(CalculoService::cbs($base, $aliquota))->toBe($esperado);
})->with([
    'valor redondo' => ['100.00', '0.0900', '9.00'],
    'meio centavo'  => ['10.05', '0.0900', '0.90'],
]);
```

## Fronteiras

- `Http::preventStrayRequests()` em testes que tocam integração; `Http::fake()` com fixtures de `tests/Fixtures/`.
- `Bus::fake()` / `Queue::fake()` para disparo; job testado chamando `handle()` direto.
- `Storage::fake()` para upload; `Event::fake()` depois de criar dados com factory.
- Dados via factories e estados; `recycle()` para compartilhar pais.
- `RefreshDatabase` só onde persiste (via `pest()->extend(...)->use(RefreshDatabase::class)->in('Feature')` se for o padrão do `tests/Pest.php`).

## O que sempre testar

- Autorização (usuário não dono → 403/404).
- Validação (422 / erros de sessão) com mensagens em PT-BR.
- Cálculo/arredondamento com valores explícitos.
- Caminho de falha da calculadora (indisponível / payload inválido).
