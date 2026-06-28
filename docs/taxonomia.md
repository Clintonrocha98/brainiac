# Taxonomia & Schema de Metadados do Catálogo

Documento vivo. Define como cada Entrada do Catálogo é classificada. Vocabulário
e definições canônicas estão no [CONTEXT.md](../CONTEXT.md); aqui fica a forma do
schema. Itens marcados **(EM ABERTO)** ainda não foram decididos.

## Princípios

- **Orientado a propósito** (ver ADR-0001): o eixo de topo é o `proposito`, não o
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
| `proposito` | enum (1 de 5) | sim | `referencia` `how-to` `explicacao` `decisao` `processo` |
| `departamento` | enum Área (1) | sim | Área dona/autora |
| `publico_alvo` | enum Área (N) | sim | Áreas + `todos` + `externo` |
| `projeto` | enum Projeto (N) | não | Vocab controlado; cruza docs por sistema/projeto |
| `palavras_chave` | lista (texto livre) | não | Único campo livre; agrupa e recupera |
| `status` | enum | sim | `rascunho` `revisão` `publicado` `obsoleto` (sinal social, sem gate — ADR-0008) |
| `owner` | pessoa | sim | Responsável pela Entrada |
| `criado_em` | data | sim | |
| `atualizado_em` | data | sim | |
| `documento` | link | condicional | Documento-fonte (markdown), renderizado pelo Brainiac. Ver invariante abaixo |
| `artefatos` | lista de link | condicional | Link(s) a Artefato (HTML). Em doc com corpo o link mora no corpo (derivado — ADR-0012); este campo é o ponteiro só pra Entrada só-artefato |
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
  iframe isolado — [ADR-0012](adr/0012-artefato-asset-html-por-link-iframe-isolado.md));
  `artefatos` como campo fica reservado à Entrada **só-artefato**.

## Facetas e seus valores

- **`proposito`**: `referencia` · `how-to` · `explicacao` · `decisao` · `processo`
- **`departamento`** (Área dona): `TI` · `Negócio` · `Produto` · `Marketing` · `Design`
- **`publico_alvo`** (leitor): as Áreas acima + `todos` + `externo`
- **`projeto`** (vocab controlado, multi-valor): a lista de projetos/sistemas da
  empresa. Genérico por área: TI = repo/módulo; Marketing = campanha/produto;
  Produto = área de produto. **(A POVOAR)** com a lista real de projetos.

## Schema da Coleção

Uma Coleção é uma view curada e **ordenada** sobre Entradas existentes (ex.: a
trilha de onboarding). Não contém conteúdo próprio — só aponta. É plana (não
aninha outras Coleções por enquanto).

| Campo | Tipo | Obrigatório | Notas |
|---|---|---|---|
| `id` | id canônico | sim | Canônico e estável |
| `slug` | texto | não | Cosmético (URL) |
| `titulo` | texto | sim | Ex.: "Onboarding Dev" |
| `resumo` | texto | sim | O que a trilha cobre e pra quem (mesmo campo da Entrada) |
| `publico_alvo` | enum Área (N) | sim | Torna a Coleção descobrível |
| `owner` | pessoa | sim | Responsável pela curadoria |
| `status` | enum | sim | `rascunho` `revisão` `publicado` `obsoleto` (sinal social, sem gate — ADR-0008) |
| `entradas` | lista ORDENADA de id | sim | A ordem é a sequência da trilha |
| `criado_em` / `atualizado_em` | data | sim | |

## Fora de escopo (por enquanto)

- Catálogo de referências de design (projeto do time de Design) — segue a mesma
  *disciplina* de metadados, mas é um store/schema separado.
- Infraestrutura do sistema (storage, render, busca) — decisão posterior; o foco
  atual é a taxonomia/schema.
