# Taxonomia & Schema de Metadados do Catálogo

Documento vivo. Define como cada Entrada do Catálogo é classificada. Vocabulário
e definições canônicas estão no [CONTEXT.md](../CONTEXT.md); aqui fica a forma do
schema. Itens marcados **(EM ABERTO)** ainda não foram decididos.

## Princípios

- **Orientado a propósito** (ver [Taxonomia de documentação orientada a propósito](adr/0001-taxonomia-orientada-a-proposito.md)): o eixo de topo é o `proposito`, não o
  departamento.
- **Facetado, não aninhado**: navegação e recuperação por filtro de facetas.
- **Vocabulário controlado**: facetas só aceitam valores de lista fechada; só
  `palavras_chave` é texto livre.
- **Serve humano e IA**: os mesmos campos alimentam a navegação humana e a
  recuperação por IA.

## Schema da Entrada

| Campo | Tipo | Obrigatório | Notas |
|---|---|---|---|
| `id` | id qualificado pela sigla | sim | Canônico e estável (ex.: `RPQ:adr/0001`, `RPQ:PRD-12`); cada origem é dona do seu id nativo |
| `slug` | texto | não | Cosmético (URL); pode mudar sem quebrar o `id` |
| `titulo` | texto | sim | Título humano da Entrada |
| `resumo` | texto (1-3 frases) | sim | Preview humano + sinal para IA |
| `proposito` | enum (1 de 3) | sim | `referencia` `how-to` `explicacao` |
| `formato` | enum (1) | sim | `README` `CONTEXT` `reference` `how-to` `explanation` `ADR` `spec` `plan` `PRD` — a forma concreta do documento; eixo distinto do `proposito`. Determina o comportamento do conteúdo (`PRD` → pilha de versões congeladas; demais → texto único) |
| `origem` | enum (1) | sim | `nativo` (escrito no Brainiac) · `espelho` (empurrado por um repo de TI via `docs:publish` — carrega ponteiro git + carimbo de sincronização) |
| `departamento` | enum Área (1) | sim | Área dona/autora |
| `publico_alvo` | enum Área (N) | sim | Áreas + `todos` + `externo` |
| `projeto` | enum Projeto (N) | não | Vocab controlado; cruza docs por sistema/projeto |
| `palavras_chave` | lista (texto livre) | não | Único campo livre; agrupa e recupera |
| `status` | enum | sim | `rascunho` `revisão` `publicado` `obsoleto` (sinal social, sem gate — [Governança do PRD social por status](adr/0008-governanca-do-prd-social-por-status.md)) |
| `owner` | pessoa | condicional | Responsável pela Entrada — obrigatório no **nativo**; no **espelho** fica nulo (o responsável vive no repo de origem) |
| `criado_em` | data | sim | |
| `atualizado_em` | data | sim | |
| `documento` | link | condicional | Documento-fonte (markdown), renderizado pelo Brainiac. Ver invariante abaixo |
| `artefatos` | lista de link | condicional | Link(s) a Artefato (HTML). Em doc com corpo o link mora no corpo (derivado — [Artefato: asset HTML por link em iframe isolado](adr/0012-artefato-asset-html-por-link-iframe-isolado.md)); este campo é o ponteiro só pra Entrada só-artefato |
| `substitui` / `substituida_por` | id (par inverso) | não | Supersessão; casa com `status: obsoleto` |
| `relacionadas` | lista de id | não | "Veja também" (simétrico) |
| `depende_de` | lista de id | não | Pré-requisito: leia/faça isto antes (direcionado) |
| `parte_de` | id | não | Composição: esta Entrada é parte de uma Entrada maior |

## Relacionamentos

- **`substitui` ↔ `substituida_por`**: par inverso. Quando uma Entrada vira
  `obsoleto`, aponta a sucessora via `substituida_por`.
- **`relacionadas`**: ligação simétrica de "veja também", sem hierarquia.
- **`depende_de`**: direcionado — pré-requisitos a consumir antes desta Entrada.
- **`parte_de`**: direcionado — composição estrutural (sub-parte de uma Entrada
  maior). **Não** é pertinência a Coleção.
- **Pertinência a Coleção** mora na Coleção (a Coleção lista suas Entradas), não
  na Entrada. Assim a mesma Entrada participa de várias Coleções sem ser editada.

## Invariantes

- Toda Entrada tem **pelo menos um** entre `documento` e `artefatos` (não pode
  existir Entrada sem nenhum conteúdo). Os dois individualmente são opcionais:
  acomoda md+html (TI), só-html (Design/Marketing) e só-md (um plano). No doc com
  corpo, o artefato entra como **link no markdown** (o Brainiac deriva e embute em
  iframe isolado — [Artefato: asset HTML por link em iframe isolado](adr/0012-artefato-asset-html-por-link-iframe-isolado.md));
  `artefatos` como campo fica reservado à Entrada **só-artefato**.

## Facetas e seus valores

- **`proposito`**: `referencia` · `how-to` · `explicacao`
- **`departamento`** (Área dona): `TI` · `Negócio` · `Produto` · `Marketing` · `Design`
- **`publico_alvo`** (leitor): as Áreas acima + `todos` + `externo`
- **`projeto`** (vocab controlado, multi-valor): a lista de projetos/sistemas da
  empresa — o **assunto** da Entrada. Genérico por área: TI = repo/módulo; Marketing =
  campanha/produto; Produto = área de produto. **(A POVOAR)** com a lista real de
  projetos. O projeto de **origem** (o que prefixa o `id`) entra automaticamente nesta
  faceta; ela pode somar outros projetos que a Entrada aborda.

## Outros campos de vocabulário controlado (não são facetas de navegação)

- **`formato`** (1): `README` · `CONTEXT` · `reference` · `how-to` · `explanation` ·
  `ADR` · `spec` · `plan` · `PRD`. A forma concreta do documento — eixo distinto do
  `proposito`. Determina o comportamento do conteúdo (`PRD` → pilha de versões;
  demais → texto único).
- **`origem`** (1): `nativo` · `espelho`. De onde o conteúdo veio — nativo (escrito
  no Brainiac) ou espelho (empurrado de um repo de TI). O espelho carrega ponteiro
  git + carimbo de sincronização.

## Schema da Coleção

Uma Coleção é uma view curada e **ordenada** sobre Entradas existentes (ex.: a
trilha de onboarding). Carrega uma **narrativa própria** (corpo markdown, nativo)
além da lista ordenada — traz contexto *e* aponta. O que a **define** é a lista
ordenada (a trilha), não o corpo. É plana (não aninha outras Coleções por enquanto).

| Campo | Tipo | Obrigatório | Notas |
|---|---|---|---|
| `id` | id canônico | sim | Canônico e estável |
| `slug` | texto | não | Cosmético (URL) |
| `titulo` | texto | sim | Ex.: "Onboarding Dev" |
| `resumo` | texto | sim | O que a trilha cobre e pra quem (mesmo campo da Entrada) |
| `corpo` | markdown (nativo) | não | Narrativa/moldura da trilha; links resolvem para outras Entradas (mesmo render dos docs) |
| `publico_alvo` | enum Área (N) | sim | Torna a Coleção descobrível |
| `owner` | pessoa | sim | Responsável pela curadoria |
| `status` | enum | sim | `rascunho` `revisão` `publicado` `obsoleto` (sinal social, sem gate — [Governança do PRD social por status](adr/0008-governanca-do-prd-social-por-status.md)) |
| `entradas` | lista ORDENADA de id | sim | A ordem é a sequência da trilha. **Só Entradas existentes** — sem itens pendentes; a curadoria é feita no Brainiac sobre o catálogo que já existe |
| `criado_em` / `atualizado_em` | data | sim | |

## Fora de escopo (por enquanto)

- Catálogo de referências de design (projeto do time de Design) — segue a mesma
  *disciplina* de metadados, mas é um store/schema separado.
- Infraestrutura do sistema (storage, render, busca) — decisão posterior; o foco
  atual é a taxonomia/schema.
