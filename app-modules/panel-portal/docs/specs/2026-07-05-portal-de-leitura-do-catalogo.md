---
type: spec
title: "Portal de leitura do catálogo (panel-portal)"
module: panel-portal
status: accepted
date: 2026-07-05
author: clintonrocha
related:
  adr: panel-portal/0002-portal-como-segundo-painel-filament
  spec: catalog/2026-07-05-modelagem-de-dados-do-catalogo
---

# Portal de leitura do catálogo (panel-portal)

## Contexto & objetivo

O domínio `catalog` está pronto (Entry, Document, PrdVersion, Project, Collection,
EntryLink, facetas e federação). Esta spec descreve a camada de **leitura e
descoberta** do catálogo: um segundo painel Filament (`/portal`), ao lado do
painel de administração/autoria (`/admin`). A implementação passou por um
protótipo inicial em Livewire full-page bespoke (ver ADR [Portal de leitura
como segundo painel Filament](../adr/0002-portal-como-segundo-painel-filament.md)
para o histórico) antes de convergir para o formato atual.

## Não-objetivos

- Render de diagrama **Mermaid** desenhado (mermaid.js) — aparece como bloco
  sinalizado "Diagrama · Mermaid" (backlog).
- **Menção-como-link** e cache de HTML renderizado (backlog).
- Gate de acesso por audience/department — todo autenticado vê tudo; facetas são
  descoberta, não muro.
- Busca sensível ao contexto atual (a busca global sempre resolve para a URL
  canônica da Entrada, não para o contexto de onde a busca foi feita).

## Arquitetura

Segundo painel Filament do projeto, módulo `panel-portal` (`He4rt\Portal`).
Importa do `catalog`; o domínio não conhece o portal. Path `/portal`, login
próprio, todo usuário autenticado tem acesso.

```
/portal  (painel Filament "portal"; guest → /portal/login)
│
├─ ÍNDICES (3 eixos; navegação lateral do Filament + busca global do Filament)
│    /portal/projects     ProjectsIndex     cards de Project (chip federação/nativo)
│    /portal/areas        AreasIndex        cards de Area (desc + nº trilhas)
│    /portal/collections  CollectionsIndex  cards de Collection (trilhas)
│
└─ CONTEXTO (sub-navigation do Filament + conteúdo)
     /portal/{eixo}/{id}            Show{Project,Area,Collection}       visão geral
     /portal/{eixo}/{id}/e/{entry}  Show{Project,Area,Collection}Entry  leitor do documento
```

### Unidades

| Unidade | Responsabilidade |
| --- | --- |
| `Filament/Pages/ContextIndexPage` (+ `ProjectsIndex`/`AreasIndex`/`CollectionsIndex`) | Base + os três índices: grid de `ContextCard` |
| `Filament/Pages/PortalContextPage` | Base: resolve o `PortalContext` a partir do parâmetro de rota, monta a sub-navigation (entradas agrupadas por propósito ou posição da trilha) |
| `Filament/Pages/ShowContextPage` (+ `ShowProject`/`ShowArea`/`ShowCollection`) | Visão geral: barra de repo (projeto), prose de introdução (trilha), trilhas da área |
| `Filament/Pages/ShowEntryPage` (+ `ShowProjectEntry`/`ShowAreaEntry`/`ShowCollectionEntry`) | Leitor: badges, banners (espelho/versão antiga), corpo prose, ligações direcionais, artefatos, anterior/próximo, rail (TOC + versões PRD + sobre + ver na fonte) |
| `Filament/Search/EntryGlobalSearchProvider` | Plugado em `->globalSearch()` do painel; usa o scope `Entry::searching()` |
| `Support/PortalContext` | Value object do contexto (project\|area\|collection): identidade, URLs (via `::getUrl()` das páginas Filament), entradas, grupos de navegação, ordem achatada p/ anterior/próximo |
| `Support/PrdVersionStack` + `PrdVersionOption` | Pilha de versões do PRD, seleção pela query `?v=`, opções do seletor |
| `Support/EntryAuthorship` | Proveniência para exibição: nativo → `owner`; espelho → `authors[]` |
| `Support/EntryLinks` + `EntryLinkItem` | Ligações tipadas nas duas direções, com rótulo conforme o sentido |
| `Support/ContextCard` + `CardChip` | Cards dos índices (badge, título, descrição, meta, chips) |
| `Support/EntryUrl` | URL canônica de uma Entrada fora do contexto atual (primeiro projeto-assunto, senão a área dona) |
| `Support/DisplayDate` | Formatação de datas no timezone de exibição |
| `Support/Markdown` | CommonMark seguro (`html_input: strip`) + pós-processamento: mermaid → bloco sinalizado, imagem → placeholder, ids nos headings; `toc()` extrai h2 fora de fences |
| `Support/SourceLink` | `{repo_url}/blob/{default_branch}/{git_pointer}` (só espelho completo) |
| `resources/css/filament/portal/theme.css` | Tema no esquema padrão do Filament; CSS do protótipo original preservado comentado, para reativação seletiva |
| `database/seeders/PortalDemoSeeder` | Dados de demonstração (3 projetos, 12 docs, 2 trilhas, versões de PRD, ligações); idempotente, só o usuário `admin` é fixo |

### Regras de apresentação

- **Proveniência:** nativo → `owner`; espelho → `authors[]` (handles com `@`).
- **Espelho é read-only**: banner fixo no leitor; badge "mirror" na navegação.
- **"Ver na fonte"** só aparece para espelho com `git_pointer` e projeto com `repo_url`.
- **PRD:** pilha de versões (`major.minor` desc); dropdown no cabeçalho + timeline no
  rail; versão fixada via query `?v=v2.0` (label completo — valor numérico puro seria
  reescrito pelo cast do Livewire); banner ao ler versão congelada antiga.
- **Ligações tipadas com rótulo direcional**: `supersedes` → "Substitui"/"Substituída
  por", `depends_on` → "Depende de"/"Dependência de", `part_of` → "Parte de"/"Contém".
- **Sem corpo** → estado vazio ("Sem documento ainda"), nunca quebra.

## Comportamento (BDD, resumo)

- Índices listam cards com contagens e chips; guest é redirecionado ao login do painel.
- Visão geral de projeto mostra barra de repo (+ "espelhado via federação" quando
  sincronizado) e docs agrupados por propósito; de área, docs do departamento + trilhas
  destinadas a ela; de trilha, posições 01, 02… e introdução em prose.
- Leitor: corpo em prose com âncoras; espelho completo mostra banner + autores +
  "Ver na fonte" composto; nativo mostra dono e nada de fonte; sem `document` mostra
  estado vazio; mermaid vira bloco sinalizado.
- PRD abre na versão mais recente; `?v=` fixa outra; banner de versão antiga com
  atalho para a mais nova.
- Entry fora do contexto da URL → 404. Área desconhecida → 404.

Cobertura: 34 testes (6 unit Markdown, 6 unit SourceLink, 22 feature).

## Trabalho futuro

- Mermaid desenhado (mermaid.js) e menção-como-link + cache no corpo renderizado.
- `Project.description` no domínio (melhora índices/visão geral).
- Busca sensível ao contexto atual.
- Federação ponta a ponta (widening do `SnapshotEntry`/`ReconcileSnapshot` para
  `audience`/`keywords`/`projects`/`status`/`slug`, hoje defaultados na ingestão) e a
  ferramenta outbound `docs:publish` nos repositórios de TI — alimenta o portal com
  conteúdo real além do seeder de demonstração.

Autoria nativa (criar/editar Entry, admin de Project/federação, gestão de
Collection, publicar/congelar PrdVersion) **não é mais trabalho futuro** — está
implementada no `panel-admin` (`EntryResource`, `ProjectResource`,
`CollectionResource`, RelationManager de `PrdVersion`).
