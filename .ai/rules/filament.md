---
paths:
  - app/Filament/**
  - app/Providers/Filament/**
---

# Filament (painel admin)

- Painel para **operação/administração** (usuários, importações, lotes, dados de referência, saúde das calculadoras). O fluxo do usuário final (cadastrar, importar, simular, comparar) é da aplicação web, não do Filament.
- Use os comandos `php artisan make:filament-*` com `--no-interaction` e a API da versão instalada (`composer show filament/filament`). Na v4+, actions ficam em `Filament\Actions\` e layout em `Filament\Schemas\Components\`.
- Páginas/Resources finas: ações que mudam estado chamam Service/Action.
- Acesso: `User::canAccessPanel()` restrito a admins em produção.
- Evite N+1 em tabelas: `->modifyQueryUsing(fn ($q) => $q->with(...))`.
- Testes com `pestphp/pest-plugin-livewire` (`livewire(ListX::class)->assertCanSeeTableRecords(...)`).
