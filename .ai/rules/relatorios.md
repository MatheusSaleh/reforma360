---
paths:
  - resources/views/relatorios/**
  - app/Services/Simulacao/RelatorioSimulacaoService.php
  - app/Http/Controllers/Simulacao/BaixarRelatorioPdfController.php
---

# Relatório em PDF (#12)

- Gerado na hora com **laravel-dompdf** (`BaixarRelatorioPdfController`); dados montados por `RelatorioSimulacaoService` (resumo, quadro por CST/cClassTrib com tratamento e fundamentação dos dados abertos, comparação com o Simples e linha do tempo quando existem, itens).
- Acima de `config('simulacao.relatorio_maximo_itens')` (padrão 1000) o detalhamento traz os itens de maior carga e avisa.
- Dados abertos fora do ar não impedem o PDF: a seção de fundamentação explica a ausência.
- **dompdf**: CSS 2.1 próprio no template (sem Tailwind), fonte DejaVu Sans (acentos), tabelas simples. `counter(pages)` **não funciona** no dompdf: o "Página X de Y" é escrito no canvas depois do `render()` (`page_text` com `{PAGE_NUM}`/`{PAGE_COUNT}`). Use `page-break-inside: avoid` em blocos curtos (título + tabela).
- Nunca habilite `isRemoteEnabled`/`isPhpEnabled` (o template tem dados do usuário).
- Testes: o conteúdo é verificado renderizando a view (`view('relatorios.simulacao', ...)->render()`); o endpoint só confere que é um PDF baixável. Para conferir o visual, renderize as páginas com Ghostscript no container (`gs -sDEVICE=png16m`).
