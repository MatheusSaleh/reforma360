---
paths:
  - app/Filament/**
  - app/Providers/Filament/**
---

# Filament (painel admin) — #13

- Filament **5** em `/admin` (`AdminPanelProvider`). Painel de **operação da plataforma** (empresas, usuários, jobs com falha, métricas). O fluxo do usuário final (cadastrar, importar, simular) é do app Blade, não do Filament.
- Acesso: `User::canAccessPanel()` → `is_admin`. O mesmo flag libera o `/horizon` em produção (`HorizonServiceProvider`). Promover: `reforma360:tornar-admin email` (`--remover`). `is_admin` **não** é mass assignable: as páginas de usuário gravam com `forceFill`, e o admin não consegue se rebaixar nem se excluir.
- Estrutura v5 gerada por `make:filament-resource`: `Resources/{X}/{XResource, Pages, Schemas/XForm, Tables/XsTable}`. Actions em `Filament\Actions\`, layout em `Filament\Schemas\Components\`, ícones `Heroicon::Outlined*`.
- Recursos: `EmpresaResource` (CNPJ com `mask` + `stripCharacters` + regra `Cnpj` + unique; sem exclusão), `UserResource` (criar envia link de redefinição de senha; ação "Enviar redefinição de senha"), `FailedJobResource` (model `FailedJob` sobre `failed_jobs`, só leitura; reprocessar/descartar via `queue:retry`/`queue:forget`).
- Widgets do dashboard (lazy): `VisaoGeral` (stats), `UsoPorDia` (ChartWidget com as cores da paleta dataviz), `EmpresasMaisAtivas` (TableWidget). Simulações de anos de linha do tempo (`linha_do_tempo_id`) não entram nas métricas.
- Testes com `Livewire::test()` (sem plugin extra): `assertCanSeeTableRecords`, `fillForm`/`call('save')`, `callAction(TestAction::make('x')->table($registro))`, `selectTableRecords` para ações em lote, `assertMountedActionModalSee` para conteúdo de modal. Widgets do dashboard carregam lazy: teste cada widget direto.
- Pint: não use `@var` em `$attributes` de model (vira tipo nativo e quebra a herança do Eloquent).
