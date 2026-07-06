---
type: spec
title: "Contrato de metadados de federação de docs"
module: catalog
status: accepted
date: 2026-07-05
author: clintonrocha
related:
  spec: catalog/2026-07-05-modelagem-de-dados-do-catalogo
  adr: catalog/0014-dois-modulos-catalog-e-apresentacao
---

# Contrato de metadados de federação de docs

## Contexto & objetivo

Repos de TI (recruit-party-quest, e futuramente todos) **empurram** seus documentos
markdown para o Brainiac via federação (PUSH → webhook → reconciliação). Cada doc vira
uma **Entry** do catálogo. Para que essa conversão seja limpa e previsível, cada doc
publicável precisa carregar um conjunto de metadados no seu front-matter YAML.

Este documento é a **definição canônica** desse contrato: quais campos, quais valores,
como cada campo vira uma faceta da Entry, e como cunhar o `id`. É a fonte da verdade única;
as guidelines de cada repo (ex.: `brainiac-doc-metadata` no recruit-party-quest) **implementam**
este contrato, e o código de ingestão (`SnapshotEntry`/`ReconcileSnapshot`) o **consome**.

Fecha o item em aberto "parse de front-matter na autoria/ingest" da spec
*Modelagem de dados do catálogo do Brainiac*.

## Não-objetivos

- Definir o transporte (HMAC, formato do webhook) — isso vive no módulo `catalog/Federation`.
- Definir a ferramenta outbound (`docs:publish`) que roda nos repos de TI — é trabalho futuro,
  fora do Brainiac.
- Metadados de docs **nativos** (escritos no próprio Brainiac): esses recebem facetas pela UI,
  não por front-matter. Este contrato vale para docs de **origem espelho**.

## Pipeline

```
DOC no repo (markdown + front-matter)
   │  docs:publish   (outbound; roda no repo de TI — trabalho futuro)
   ▼
POST /webhook/snapshot   (assinado, HMAC)
   │  webhook → ReceiveSnapshotController   (Brainiac)
   ▼
JSON → SnapshotEntry (DTO)   ← a "largura do cano"
   ▼
ReconcileSnapshot  → upsert de Entry (origem = mirror)
```

## Campos do contrato

| Campo | Obrigatório | Vira (Entry) | Notas |
| --- | --- | --- | --- |
| `title` | sim | `title` | Título humano. |
| `summary` | sim | `summary` | 1-3 frases. É o principal sinal para recuperação por IA quando o doc não tem corpo renderizado — escreva bem. |
| `format` | sim | `format` | A forma concreta do doc (ver enum). Substitui a antiga chave `type`. |
| `purpose` | sim | `purpose` | Eixo Diátaxis (ver enum). |
| `department` | sim | `department` | Área dona. Default por repo (ex.: `ti` no recruit-party-quest). |
| `id` | recomendado | `qualified_id` | native_id **sem sigla**; o Brainiac prefixa a sigla do repo → `<SIGLA>:<id>`. Se omitido, o publisher deriva do path. |
| `audience` | não → `[department]` | `audience` | Áreas que devem descobrir o doc. |
| `keywords` | recomendado | `keywords` | Único campo de texto livre. |
| `status` | não → `published` | `status` | Vocabulário do Brainiac (ver enum + mapeamento). |
| `projects` | não | faceta `project` | Siglas de outros projetos que o doc aborda; a sigla de origem entra automaticamente (invariante origem-na-faceta). |
| `related` | não | `relacionadas` (veja também) | Lista de ids (simétrico). |
| `supersedes` / `depends_on` / `part_of` | não | `EntryLink` tipado | Lista(s) de ids. |
| `authors` | sim | `authors` (jsonb) | Handles do GitHub (ex.: `joazinho123`) de quem criou/editou; o Claude anexa o handle a cada edição (dedup). Espelho usa `authors`, **não** `owner`. |

> **Nível-snapshot (não por doc):** `repo_url` e `default_branch` — o publisher auto-reporta do
> `git remote`; o Brainiac grava no `Project` e compõe o link à fonte
> `{repo_url}/blob/{default_branch}/{git_pointer}`.
> **`owner`** (único, `owner_id`) é conceito **nativo** — no espelho fica `null`.

## Vocabulário (deve casar exatamente com os enums do Brainiac)

- `purpose`: `reference` · `how-to` · `explanation`
- `format`: `readme` · `context` · `architecture` · `reference` · `how-to` · `explanation` · `adr` · `spec` · `plan` · `prd`
- `department` / `audience` (`Area`): `ti` · `business` · `product` · `marketing` · `design`; `audience` também aceita `all` e `external`
- `status`: `draft` · `review` · `published` · `obsolete`

## Mapeamento doc → facetas

| Arquivo no repo | `format` | `purpose` | `id` (native_id) |
| --- | --- | --- | --- |
| `README.md` (módulo) | `readme` | `reference` | `<module>/readme` |
| `CONTEXT.md` | `context` | `reference` | `<module>/context` |
| `ARCHITECTURE.md` | `architecture` | `explanation` | `<module>/architecture` |
| `docs/adr/NNNN-*.md` | `adr` | `explanation` | `<module>/adr/NNNN` |
| `docs/specs/AAAA-MM-DD-*.md` | `spec` | `reference` | `<module>/spec/<slug>` |
| `docs/plans/AAAA-MM-DD-*.md` | `plan` | `how-to` | `<module>/plan/<slug>` |
| `docs/prd/*.md` | `prd` | `reference` | `<module>/prd/<slug>` |
| Diátaxis (`reference`/`how-to`/`explanation`) | = purpose | conforme autoria | `<module>/<purpose>/<slug>` |

## Convenção de `native_id`

- Estável e único no repo; preferir **derivado do path** para sobreviver a edições de conteúdo.
- Sem prefixo de sigla — o Brainiac prefixa a sigla do Projeto do repo.
- Preservar em renomeações: mudar o slug é cosmético; o `id` é a identidade do doc.

## Mapeamento de `status` (ciclo do repo → Brainiac)

| Ciclo no repo | `status` Brainiac |
| --- | --- |
| ADR accepted / spec publicada / plan completed | `published` |
| draft / proposed | `draft` |
| em revisão / in_progress | `review` |
| superseded / deprecated / rejected | `obsolete` (+ link `supersedes` quando aplicável) |

## Camadas de ingestão (hoje vs alvo)

O `SnapshotEntry` **hoje** carrega: `qualified_id`, `native_id`, `title`, `summary`, `purpose`,
`format`, `department`, **`authors`** (+ corpo markdown e ponteiro git). No nível do snapshot
também trafegam **`repo_url`** e **`default_branch`** (o publisher auto-reporta do `git remote`),
gravados no `Project`. Ainda **defaultados** na ingestão: `audience` (→ `[department]`),
`status` (→ `published`), `keywords`, `projects`.

Estratégia em duas camadas:

- **Autoria (repos, agora):** os docs nascem com o front-matter **completo**. É barato e o doc
  é a fonte da verdade — não queremos retocar todos quando o cano alargar.
- **Ingestão (Brainiac):** já alargado para `authors` + `repo_url`/`default_branch`; falta ainda
  `audience`/`keywords`/`projects`/`status`/`slug`.

## Decisões

- **`format` substitui `type`.** Mesma ideia (forma concreta do doc), mas cobre todos os tipos
  (README, CONTEXT, ARCHITECTURE…), não só spec/plan/adr/prd. `type` é considerado legado.
- **`architecture` adicionado ao enum `Format`.** ARCHITECTURE.md é forma concreta reconhecida;
  preservá-la (em vez de colapsar em `explanation`) é coerente com `format` ser eixo distinto de
  `purpose`. `purpose` de um ARCHITECTURE.md continua `explanation`.
- **`native_id` derivado do path**, estável, sem sigla.
- **`audience` default `[department]`**; **`status` default `published`**.
- **`authors` = lista de handles do GitHub** (ex.: `joazinho123`). O Claude anexa quem edita
  (dedup, criador primeiro). Espelho usa `authors`; **`owner` (único) é conceito nativo**, fica
  `null` no espelho. Histórico de edição é do git.
- **`repo_url` + `default_branch` no `Project`**, auto-reportados pelo publisher (`git remote`),
  para compor o link à fonte `{repo_url}/blob/{default_branch}/{git_pointer}`.

## Trabalho pendente no Brainiac

- Estender `SnapshotEntry`/controller/`ReconcileSnapshot` para os campos **ainda** defaultados
  (`audience`/`keywords`/`projects`/`status`/`slug`). `authors` e `repo_url`/`default_branch` já implementados.
- Ferramenta outbound `docs:publish` (nos repos de TI): lê o front-matter, deriva `repo_url`/
  `default_branch` do `git remote`, e monta o snapshot assinado (HMAC).
- Migrar docs legados que ainda usam `type` para `format` quando forem tocados.
