---
type: spec
title: "Portal de leitura do catálogo (panel-portal)"
module: panel-portal
status: accepted
date: 2026-07-05
author: clintonrocha
related:
  adr: panel-portal/0001-portal-de-leitura-proprio
  spec: catalog/2026-07-05-modelagem-de-dados-do-catalogo
---

# Portal de leitura do catálogo (panel-portal)

## Contexto & objetivo

O domínio `catalog` está pronto (Entry, Document, PrdVersion, Project, Collection,
EntryLink, facetas e federação). Esta spec descreve a **primeira camada de
apresentação**: um portal de **leitura e descoberta**, implementado a partir do
protótipo aprovado no Claude Design (*Catálogo Brainiac v2*). Autoria (criar/editar,
definir `owner`) fica explicitamente fora — é fatia futura, no `panel-admin`.

O portal é **read-only por decisão de foco**: o conteúdo inicial chega pela federação
(espelhos), então o valor imediato é encontrar e ler bem.

## Não-objetivos

- Autoria nativa (criar/editar Entry, editor de corpo, cunhagem de `qualified_id` na UI).
- Render de diagrama **Mermaid** (aparece como bloco sinalizado "Diagrama · Mermaid").
- **Menção-como-link** e cache de HTML renderizado (backlog).
- Gate de acesso por audience/department — todo autenticado vê tudo; facetas são
  descoberta, não muro.
- Administração da federação (rotação de segredos etc.) — fatia do `panel-admin`.

## Arquitetura

Módulo `panel-portal` (`He4rt\Portal`), presentation, Livewire 4 + Blade + Tailwind v4.
Importa do `catalog`; o domínio não conhece o portal.

```
/portal  (middleware web + auth; guest → login do Filament)
│
├─ ÍNDICES (3 eixos, topbar com abas + busca global + status da federação)
│    /portal/projects     ProjectsIndex     cards de Project (chip federação/nativo)
│    /portal/areas        AreasIndex        cards de Area (desc + nº trilhas)
│    /portal/collections  CollectionsIndex  cards de Collection (trilhas)
│
└─ CONTEXTO (grid 268px | prose | 236px)
     /portal/{eixo}/{id}            ShowContext  visão geral do contexto
     /portal/{eixo}/{id}/e/{entry}  ShowEntry    leitor do documento
```

### Unidades

| Unidade | Responsabilidade |
| --- | --- |
| `Support/PortalContext` | Value object do contexto (project\|area\|collection): identidade, URLs, entradas, grupos de navegação (por purpose ou posição da trilha), ordem achatada p/ anterior/próximo |
| `Support/Markdown` | CommonMark seguro (`html_input: strip`) + pós-processamento: mermaid → bloco sinalizado, imagem → placeholder, ids nos headings; `toc()` extrai h2 fora de fences |
| `Support/SourceLink` | `{repo_url}/blob/{default_branch}/{git_pointer}` guardado (só espelho completo) |
| `Livewire/*Index` | Os três índices (cards uniformes) |
| `Livewire/ShowContext` | Visão geral: barra de repo (projeto), prose de introdução (trilha), trilhas da área |
| `Livewire/ShowEntry` | Leitor: badges, banners (espelho/versão antiga), corpo prose, ligações direcionais, artefatos, anterior/próximo, rail (TOC + versões PRD + sobre + ver na fonte) |
| `Livewire/GlobalSearch` | Busca por título/summary/qualified_id/keywords (6 resultados) |
| `resources/css/portal.css` | Tema (tokens do design: fundo #0F0E14, acento #A48FFA, ciano espelho #56C2D6; Instrument Sans/Source Serif 4/JetBrains Mono) + prose |
| `database/seeders/PortalDemoSeeder` | Dados de demonstração idênticos ao protótipo (3 projetos, 12 docs, 2 trilhas, versões de PRD, ligações) |

### Regras de apresentação

- **Proveniência:** nativo → `owner`; espelho → `authors[]` (handles com `@`).
- **Espelho é read-only**: banner ciano fixo no leitor; dot ciano na navegação.
- **"Ver na fonte"** só aparece para espelho com `git_pointer` e projeto com `repo_url`.
- **PRD:** pilha de versões (`major.minor` desc); dropdown no cabeçalho + timeline no
  rail; versão fixada via query `?v=v2.0` (label completo — valor numérico puro seria
  reescrito pelo cast do Livewire); banner âmbar ao ler versão congelada antiga.
- **Ligações tipadas com rótulo direcional**: `supersedes` → "Substitui"/"Substituída
  por", `depends_on` → "Depende de"/"Dependência de", `part_of` → "Parte de"/"Contém".
- **Sem corpo** → estado vazio ("Sem documento ainda"), nunca quebra.

## Divergências deliberadas em relação ao protótipo

1. **Descrição do projeto**: o protótipo exibia uma descrição rica; `Project` não tem
   esse campo — usamos `technical_name`. Campo `description` é backlog de domínio.
2. **Busca**: o resultado navega para o contexto primário do alvo (primeiro projeto da
   faceta, senão a área); o protótipo preservava o contexto atual quando possível.
3. **Tabelas GFM** são renderizadas de verdade (o protótipo pulava linhas `|`).
4. **Status da federação** no topo só aparece quando algum projeto já sincronizou.
5. Datas dinâmicas (`diffForHumans`, `translatedFormat('d M Y')` no fuso de exibição).

## Comportamento (BDD, resumo)

- Índices listam cards com contagens e chips; guest é redirecionado ao login.
- Visão geral de projeto mostra barra de repo (+ "espelhado via federação" quando
  sincronizado) e docs agrupados por propósito; de área, docs do departamento + trilhas
  destinadas a ela; de trilha, posições 01, 02… e introdução em prose.
- Leitor: corpo em prose com âncoras; espelho completo mostra banner + autores +
  "Ver na fonte" composto; nativo mostra dono e nada de fonte; sem `document` mostra
  estado vazio; mermaid vira bloco sinalizado.
- PRD abre na versão mais recente; `?v=` fixa outra; banner de versão antiga com
  atalho para a mais nova.
- Entry fora do contexto da URL → 404. Área desconhecida → 404.

Cobertura: 33 testes (6 unit Markdown, 6 unit SourceLink, 21 feature).

## Trabalho futuro

- Autoria nativa no `panel-admin` (Filament) e admin de Projects/federação.
- Mermaid desenhado (mermaid.js) e menção-como-link + cache no `<x-panel-portal::markdown>`.
- `Project.description` no domínio (melhora índices/visão geral).
- Busca sensível ao contexto atual.
