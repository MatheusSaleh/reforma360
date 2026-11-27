---
paths:
  - app/Models/**
  - app/Enums/**
---

# Models e Enums

- Mass assignment explícito (`$fillable`/`#[Fillable]` ou `$guarded`). Nunca `$guarded = []` nem `Model::unguard()` global.
- Casts no método `casts()`: datas, enums, `decimal:2` para dinheiro em decimal, `decimal:4` para alíquotas, `encrypted` para dado sensível quando couber. **Nunca** `float`/`double`.
- NCM, NBS, CNPJ, CPF: `string` só com dígitos. Formatação via accessor/`Attribute` só para exibição.
- Relacionamentos com tipo de retorno (`HasMany`, `BelongsTo`) e PHPDoc genérico para o Larastan (`@return HasMany<ItemSimulacao, $this>`).
- `BelongsTo` com FK NOT NULL: declare `@property-read Empresa $empresa` no docblock da classe; sem isso o Larastan nível 8 vê `Empresa|null`. O docblock vai **antes** dos atributos PHP (`#[Fillable]`, `#[Table]`); entre o atributo e a classe ele é ignorado.
- Scopes com `#[Scope]` ou `scopeX` conforme a versão instalada; não use global scopes que escondem dados sem necessidade.
- Status com **backed enum** em `App\Enums` (chaves TitleCase), ex.: `StatusImportacao::Processando`, `Regime::SimplesNacional`. Enum pode ter `label()` em PT-BR.
- Toda model nova tem factory com estados úteis (`->simulado()`, `->comErro()`).
- Model não chama HTTP, não dispara job pesado e não contém regra tributária — isso é Service.
