---
type: adr
title: "Portal de leitura como segundo painel Filament"
module: panel-portal
status: accepted
date: 2026-07-06
author: clintonrocha
related:
  adr: panel-portal/0001-portal-de-leitura-proprio
  spec: panel-portal/2026-07-05-portal-de-leitura-do-catalogo
---

# ADR 0002 — Portal de leitura como segundo painel Filament

## Contexto

A ADR [Leitura do catálogo em módulo próprio](0001-portal-de-leitura-proprio.md)
decidiu que o portal seria Livewire 4 full-page + Blade + Tailwind v4, com tema
escuro, tipografia (Instrument Sans/Source Serif 4/JetBrains Mono) e chrome
(topbar, sidebar, busca) todos bespoke, fiéis ao protótipo aprovado no Claude
Design ("Catálogo Brainiac v2"). Nessa mesma sessão, a autoria do catálogo foi
implementada no `panel-admin` (Filament). Pedido explícito do usuário: levar o
portal de leitura para dentro do Filament também — reaproveitar chrome, tema,
RBAC, busca global e componentes de UI em vez de manter duas pilhas de
apresentação paralelas (Livewire bespoke vs. Filament) dentro do mesmo produto.

## Decisão

O `panel-portal` continua sendo o módulo de apresentação da **leitura** do
catálogo — o corte de módulos das ADRs [Dois módulos: catalog e
apresentação](../../../../docs/adr/0014-dois-modulos-catalog-e-apresentacao.md)
e 0001 não muda. O que muda é a implementação: de Livewire full-page bespoke
para um **segundo painel Filament** (`portal`, `He4rt\Portal\Filament\`).

- **Painel próprio** (`App\Providers\Filament\PortalPanelProvider`), path
  `/portal`, login dedicado (`LoginPage` compartilhada com o admin), todo
  usuário autenticado tem acesso (`User::canAccessPanel` → `true` para
  `FilamentPanel::Portal`).
- **Páginas Filament** (`Filament\Pages\Page`), não componentes Livewire
  soltos. Os três índices (Projetos/Áreas/Trilhas) e o par contexto/leitor por
  eixo, com rota via slug parametrizado (ex.:
  `projects/{project:slug}/e/{entry}`). Rotas passam a seguir a convenção do
  Filament (`filament.portal.pages.*`) em vez de nomes próprios (`portal.*`).
- **Navegação lateral do contexto** via sub-navigation nativa do Filament
  (`getSubNavigation()`), com badges/ícones lidos direto dos enums do domínio
  (`Purpose`/`Origin`/`Status`/`PrdVersionState` implementam `HasColor`;
  `Format` implementa `HasIcon`) em vez de Blade condicional bespoke.
- **Busca global** via `GlobalSearchProvider` custom
  (`EntryGlobalSearchProvider`), plugado em `->globalSearch()` do painel — o
  Filament renderiza o campo/dropdown; o provider só busca (usa o scope de
  domínio `Entry::searching()`).
- **Tema no esquema padrão do Filament**
  (`resources/css/filament/portal/theme.css`, registrado via
  `->viteTheme()`): cores e fontes voltam a ser as do Filament, não as do
  design escuro custom. O CSS do protótipo original fica **preservado,
  comentado, no mesmo arquivo** — reativação seletiva é possível se o visual
  padrão não for satisfatório.
- **Manipulação de estado extraída para value objects** em
  `panel-portal/src/Support/` (`PrdVersionStack`, `EntryAuthorship`,
  `EntryLinks`/`EntryLinkItem`, `ContextCard`/`CardChip`, `EntryUrl`,
  `DisplayDate`), tirando lógica das páginas — consequência de código mais
  limpo dentro do novo modelo, não uma decisão arquitetural em si.

## Consequências

- Supera a ADR 0001: a exigência de tema/tipografia/chrome bespoke deixa de
  valer para este módulo.
- Perda de fidelidade visual ao protótipo do Claude Design em troca de menos
  CSS/JS para manter e reaproveito do chrome, RBAC e componentes do Filament.
- Fecha o item "autoria nativa" antes listado como trabalho futuro na spec do
  portal: autoria já vive no `panel-admin` (`EntryResource`, `ProjectResource`,
  `CollectionResource`, RelationManager de `PrdVersion`).
- Segue valendo: apresentação (`panel-portal`, `panel-admin`) importa do
  domínio (`catalog`); o domínio não conhece nenhum dos dois.
