---
type: spec
title: "Modelagem de dados do catálogo do Brainiac"
module: catalog (domínio + federação; apresentação no panel-admin)
status: in_progress
date: 2026-07-05
author: clintonrocha
related:
  - CONTEXT.md
  - docs/taxonomia.md
  - docs/adr/0013-brainiac-single-tenant.md
  - docs/adr/0014-dois-modulos-catalog-e-apresentacao.md
---

# Modelagem de dados do catálogo do Brainiac

## Contexto

Este spec traduz o design já cristalizado (glossário em `CONTEXT.md`, 12 ADRs,
`taxonomia.md`, `arquitetura.md`, `federacao.md`) em um **modelo de dados** concreto,
para começar a implementação. As decisões abaixo saíram de uma sessão de
grill-with-docs. A **separação de módulos** está decidida — ver
[Brainiac se organiza em dois módulos](../adr/0014-dois-modulos-catalog-e-apresentacao.md):
dois módulos, `catalog` (domínio + federação como sub-namespace `Federation/`) e a
apresentação no `panel-admin`.

## Convenção de nomenclatura

- **Docs e glossário permanecem em pt_BR** (linguagem ubíqua + guideline de doc).
- **Identificadores de código** (tabela, coluna, model, enum) em **inglês claro** —
  sem palavra rebuscada. Enums: cases em inglês, labels via i18n pt_BR.
- Mapa glossário↔código: Entrada→`entries` · Documento→`documents` ·
  Projeto→`projects` · Coleção→`collections` · Propósito→`purpose` · Formato→`format` ·
  Origem→`origin` · departamento→`department` · publico_alvo→`audience` ·
  palavras_chave→`keywords` · sigla→`acronym`.

## Precondições (arquitetura de base)

- **Single-tenant** — ver [Brainiac é single-tenant](../adr/0013-brainiac-single-tenant.md).
  Nenhuma tabela do catálogo tem `tenant_id`. `Project` é entidade comum, não tenant.
- **Scaffold podado:** mantém `User` + `Permissions` (acesso ao painel / autor × leitor,
  **não** gate de publish do PRD); removem-se `Teams` (era o tenant) e `ExternalIdentity`
  (OAuth Discord). `owner` de Entrada/Coleção = FK para `users`.
- **PK = UUID** (`HasUuids`, ordenado) em toda tabela — casa com o scaffold.

## Princípio central: ficha × conteúdo

A **Entrada** (`entries`) é a *ficha* do catálogo (metadados). O **conteúdo** mora à
parte e é **polimórfico**:

```
  entries (ficha — metadados universais, SEMPRE editável no lugar)
     │
     ├── documents (1:1) ...... corpo markdown único      [origin: native | mirror]
     └── prd_versions (1:N) .... pilha de versões congeladas [só quando format = prd]
                                 (ou só-artefato: sem corpo, só link)
```

A Entrada é a **raiz de agregado única** (o catálogo é uniforme). Um PRD **é** uma
Entrada com a pilha pendurada — não um agregado à parte. Invariante: toda Entrada tem
≥1 conteúdo entre `{document, prd_versions, artifact}`.

## Tabelas

### `projects` (Projeto)
`id` (uuid) · `business_name` · `technical_name` · `slug` · `acronym` (único; **não**
pode ser igual a um valor de `Area`) · `webhook_token` (hash) · `hmac_secret` (cifrado)
· `last_synced_at` (nullable) · timestamps.
> A `acronym` é o handle canônico e a origem dos ids — ver
> [Projeto é entidade de 1ª classe](../adr/0006-projeto-primeira-classe-sigla-canonica.md).
> Credencial + carimbo de sync moram aqui (federação se anuncia pela acronym).

### `entries` (Entrada)
- `id` (uuid, PK interno, usado nas FKs)
- `qualified_id` (string, **único, imutável**, cunhado uma vez) — ex.: `RPQ:PRD-12`,
  `DESIGN:how-to/handoff`
- `native_id` (string) — ex.: `PRD-12`, `adr/0001`
- `project_id` (uuid, FK nullable) — o projeto de **origem** (prefixo do id). Quando
  nulo, o prefixo cai para `department` (Área). Ver campo `id` no `CONTEXT.md`.
- `slug` (string, cosmético)
- `title` · `summary`
- `purpose` (enum: `reference` | `how-to` | `explanation`)
- `format` (enum: `readme|context|reference|how-to|explanation|adr|spec|plan|prd`)
- `origin` (enum: `native` | `mirror`)
- `department` (enum `Area`) — dono (1)
- `audience` (json array de `Area` + `All` + `External`)
- `keywords` (json array de string, texto livre)
- `status` (enum: `draft|review|published|obsolete`) — sinal **social** (nível Entrada)
- `owner_id` (uuid, FK users, **nullable** — obrigatório no nativo; nulo no espelho, cujo responsável vive no repo de origem) · timestamps

### `documents` (Documento — corpo único; native evergreen + mirror)
`id` · `entry_id` (FK 1:1) · `body_markdown` · `git_pointer` (nullable — só mirror) ·
**fatos do corpo derivados no ingest/save e guardados**: `has_image`, `has_mermaid`,
`has_artifact` (bool) + `mentions` (json — ids/paths citados no corpo) · timestamps.
> HTML renderizado **não** fica no banco: cache do Laravel por hash do markdown
> (ver [Markdown canônico, render centralizado](../adr/0010-markdown-canonico-render-centralizado.md)).

### `prd_versions` (pilha do PRD — só `format = prd`)
`id` · `entry_id` (FK 1:N) · `major` (int nullable) · `minor` (int nullable) ·
`body_markdown` · `state` (enum: `draft` | `frozen`) · `frozen_at` (nullable) ·
mesmos fatos do corpo do `documents` · timestamps.
- **Status em dois níveis:** `state` por versão (`draft`|`frozen`) + `status` social na
  ficha. Um `draft` novo **não** rebaixa o `status` publicado da Entrada.
- **Numeração:** `major`/`minor` inteiros; `draft` sem número até publicar; o sistema
  calcula o próximo a partir da última `frozen` + a declaração maior/menor do Produto
  (maior→`major+1,minor=0`; menor→`minor+1`). 1ª publicação = `v1.0`. No máximo 1 `draft`
  por Entrada. Maior/menor é **declarado pelo humano** (detecção automática exigiria IA e
  é não-confiável — ver [Ciclo de vida do PRD](../adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md)).
- **PRD:** `purpose = reference` (fixo); a Visão de produto é o par `explanation`.

### `entry_links` (grafo entre Entradas)
`id` · `from_entry_id` (FK) · `to_entry_id` (FK — **tem que existir**) ·
`type` (enum: `supersedes|related|depends_on|part_of`).
- **FK rígida, só alvo existente** (decisão de simplicidade). `superseded_by` = consulta
  inversa de `supersedes`; `related` (simétrica) = consulta nos dois sentidos. Não se
  guarda o lado inverso.
- **Federação:** as ligações são derivadas do front-matter **no ingest**; referência a
  alvo ainda não espelhado é **ignorada** (não vira linha) e se cura no re-publish /
  re-derivação (snapshot é completo e idempotente).

### `entry_project` (pivot — faceta `projeto`, multi-valor)
`entry_id` (FK) · `project_id` (FK). A faceta `projeto` = **assunto** (N projetos sobre
os quais a Entrada fala), distinta do `entries.project_id` = **origem** (quem emitiu,
cunha o id, 0..1).
- **Invariante: a origem sempre está na faceta.** Se `entries.project_id` existe,
  uma linha correspondente em `entry_project` é **garantida automaticamente** (na
  criação e no ingest da federação). A faceta pode somar outros projetos (aboutness).
- **Consequência:** navegar "por projeto X" é **uma consulta só** no pivot, sempre
  completa — nunca perde um doc que X emitiu. Não há `OR project_id` na busca.
  `project_id` fica reservado à proveniência / cunhagem do id.

### `entry_artifacts` (só Entrada só-artefato)
`entry_id` (FK) · `url`. Para doc **com corpo**, o artefato é **derivado** do link no
markdown (não é campo) — ver
[Artefato: asset HTML por link em iframe isolado](../adr/0012-artefato-asset-html-por-link-iframe-isolado.md).

### `collections` (Coleção) + `collection_entry`
`collections`: `id` · `slug` · `title` · `summary` · `body_markdown` (narrativa própria,
nativa) · `audience` (json) · `owner_id` · `status` · **fatos do corpo derivados**
(`has_image`, `has_mermaid`, `has_artifact`, `mentions`) · timestamps.
`collection_entry`: `collection_id` (FK) · `entry_id` (FK — **existente**) · `position`
(int, ordena a trilha). **Só Entradas existentes**, sem itens pendentes.
- **Carrega corpo próprio** (`body_markdown`, nativo) além da lista ordenada: traz
  narrativa/contexto *e* aponta. Os links no corpo resolvem para outras Entradas como
  em qualquer doc nativo (mesmo pipeline de render / mesmos fatos do corpo).
- **Fronteira com a Entrada:** o que **define** a Coleção é a lista ordenada de Entradas
  (trilha navegável, reutilizável, com next/prev) — a Entrada não tem isso; o corpo é só
  a moldura. Coleção **não é Entrada** (sem `purpose`/`format`/facetas de propósito), é
  objeto de curadoria, e não aninha outra Coleção.

## Vocabulários controlados (PHP enums)

`Purpose` · `Format` · `Origin` (`Native`/`Mirror`) · `Area`
(`Ti`/`Business`/`Product`/`Marketing`/`Design`; `audience` acrescenta `All`/`External`)
· `Status` (`Draft`/`Review`/`Published`/`Obsolete`). Cases em inglês, labels pt_BR via
i18n. `project` é tabela (entidade), não enum.

## Federação (mecânica de dados)

Push pelo módulo — ver
[Federação por PUSH pelo módulo](../adr/0009-federacao-por-push-modulo.md). Mora no
sub-namespace `Federation/` do módulo `catalog` (é lógica de domínio; ver
[Brainiac se organiza em dois módulos](../adr/0014-dois-modulos-catalog-e-apresentacao.md)).
No ingest de um
snapshot **completo** de um projeto: **upsert** do que veio (casando por `qualified_id`)
+ **apaga** os espelhos daquele projeto **ausentes** no snapshot, tudo numa **transação**,
escopo estrito `(project, origin=mirror)` — nunca toca nativos nem outros projetos.
Idempotente; deleção propaga.

## Render e referências no corpo

- Markdown é canônico; render-on-read com cache por hash (Laravel cache), HTML descartável.
- **Fatos do corpo** (`has_*`, `mentions`) derivados no ingest/save e **guardados**.
- **Menção no corpo** (link markdown dentro do texto) é resolvida **no render**: caminho
  relativo de repo → casa com o `git_pointer` de um espelho → URL da Entrada no Brainiac;
  `qualified_id` → busca a Entrada. Alvo não espelhado → texto simples. O cache de render
  de um doc é invalidado quando um alvo que ele menciona (via `mentions`) passa a existir.

## Itens abertos / próximos

1. ✓ RESOLVIDO — **Separação de módulos**: dois módulos (`catalog` domínio+federação,
   apresentação no `panel-admin`). Ver
   [Brainiac se organiza em dois módulos](../adr/0014-dois-modulos-catalog-e-apresentacao.md).
2. ✓ RESOLVIDO — `project_id` (origem/proveniência, 0..1) × faceta `projeto` (assunto,
   N) são distintos, com invariante "**origem sempre na faceta**" (garantida
   automaticamente). Navegação por projeto = consulta única no pivot. Ver seção
   `entry_project` acima.
3. ✓ RESOLVIDO — Visão de produto = Entrada `explanation` comum, **sem marcador** no
   schema (opção B). "Uma por Projeto / acima dos PRDs" é curadoria + apresentação; se
   um dia precisar distinguir, `format: vision` é aditivo. A Visão é narrativa e **não**
   indexa os PRDs — isso é a Coleção-com-corpo / a página do projeto (índice/roadmap
   nativo = Coleção com `body_markdown`; README continua espelho do TI).
4. **Autoria por guideline / parse do front-matter** — é fluxo (Action/ingest), não
   tabela nova; detalhar quando for implementar.
5. ✓ RESOLVIDO — a guideline `.ai/guidelines/domain/03-multi-tenancy.blade.php` foi
   reescrita para "Single-Tenant — No Tenancy" (avisa para não reintroduzir `tenant_id`).
