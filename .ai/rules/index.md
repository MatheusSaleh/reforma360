# Project Rules Index

Antes de planejar ou editar, encontre as linhas cujos globs cobrem o caminho do arquivo e leia a regra. Rode também `grep -rin '<palavra>' .ai/rules` para pegar o que o glob não cobre.

| Aplica-se a | Regra |
| --- | --- |
| app/Http/Controllers/**, app/Http/Requests/**, app/Services/**, app/Actions/** | .ai/rules/controllers.md |
| routes/** | .ai/rules/routes.md |
| app/Models/**, app/Enums/** | .ai/rules/models.md |
| database/migrations/**, database/seeders/**, database/factories/** | .ai/rules/database.md |
| app/Integrations/**, config/services.php | .ai/rules/integrations.md |
| app/Importers/**, app/Jobs/Import*/** | .ai/rules/importers.md |
| app/Jobs/**, app/Events/**, app/Listeners/**, config/horizon.php, config/reverb.php | .ai/rules/jobs.md |
| routes/api.php, app/Http/Controllers/Api/**, app/Http/Resources/** | .ai/rules/api.md |
| app/Filament/**, app/Providers/Filament/** | .ai/rules/filament.md |
| app/Models/**, app/Policies/**, app/Http/Controllers/**, app/Actions/Fortify/** | .ai/rules/tenancy.md |
| resources/views/relatorios/**, app/Services/Simulacao/RelatorioSimulacaoService.php | .ai/rules/relatorios.md |
| app/Providers/** | .ai/rules/providers.md |
| tests/** | .ai/rules/tests.md |
