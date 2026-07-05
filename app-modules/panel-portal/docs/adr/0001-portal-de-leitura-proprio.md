---
type: adr
title: "Leitura do catálogo em módulo próprio (panel-portal), não no Filament"
module: panel-portal
status: accepted
date: 2026-07-05
author: clintonrocha
related:
  adr: 0014-dois-modulos-catalog-e-apresentacao
---

# ADR 0001 — Leitura do catálogo em módulo próprio (panel-portal), não no Filament

## Contexto

O ADR *Dois módulos: catalog e apresentação* (root, 0014) previa a apresentação do
catálogo dentro do `panel-admin` (Filament). Ao desenhar a experiência de leitura no
Claude Design ("Catálogo Brainiac v2"), o resultado aprovado foi um **portal de
documentação** com identidade própria: navegação por três eixos (Projetos, Áreas,
Trilhas), layout de leitura em três colunas (sidebar de contexto + prose + rail),
tipografia dedicada e tema escuro específico — nada disso é o chrome do Filament.

## Decisão

A **leitura/descoberta** do catálogo vive num módulo de apresentação próprio,
**`panel-portal`** (`He4rt\Portal`): Livewire 4 full-page + Blade + Tailwind v4, com
tema próprio (`resources/css/portal.css`) e rotas sob `/portal` (autenticadas).

O `panel-admin` (Filament) segue sendo o lar da **administração e da futura autoria**
(criar/editar Entries nativas, gestão de Projects/federação).

## Consequências

- Supera **parcialmente** o ADR 0014 (root): "apresentação no panel-admin" passa a
  valer só para admin/autoria; a leitura é do `panel-portal`.
- Fidelidade ao design aprovado sem brigar com o Filament (que viraria só um shell).
- Um asset de CSS a mais no Vite (`portal.css`) e fontes próprias (Instrument Sans,
  Source Serif 4, JetBrains Mono).
- Presentation importa do domínio (`catalog`), nunca o contrário — inalterado.
