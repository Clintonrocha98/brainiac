# Exemplos de Entradas (validação do schema)

Casos escolhidos para estressar o schema. `⚠ ATRITO` marca uma tensão real do
schema ainda em aberto. Dados são placeholders realistas — o que importa aqui é a
*forma*.

---

## Referência: módulo de Pagamentos

```yaml
id: RPQ:pagamentos/reference/modulo-pagamentos
slug: modulo-pagamentos
titulo: "Módulo de Pagamentos — referência técnica"
resumo: "Contratos, eventos e configuração do módulo de Pagamentos."
proposito: referencia
departamento: TI
publico_alvo: [TI]
projeto: [RPQ]
module: pagamentos
palavras_chave: [pagamentos, gateway, webhook, idempotencia]
status: publicado
owner: ana@empresa.com
documento: repo://modules/payments/README.md
relacionadas: [DESIGN:how-to/handoff-design-dev]
```

> ⚠ ATRITO 1 — O README real do módulo mistura **referência** (contratos),
> **how-to** (como rodar local) e **explicação** (por que idempotência). Mas
> `proposito` é valor único (MECE). Esse README não é UMA Entrada — são três.

---

## How-to: handoff Design → Dev

```yaml
id: DESIGN:how-to/handoff-design-dev
slug: handoff-design-dev
titulo: "Handoff de Design para Desenvolvimento"
resumo: "Rito de passagem de uma tela do Figma aprovada para a fila de dev."
proposito: how-to
departamento: Design          # dono do rito
publico_alvo: [Design, TI]    # bilateral — os dois lados seguem
projeto: []                   # fluxo cross-área não é "de um projeto"
palavras_chave: [handoff, figma, design-system, definicao-de-pronto]
status: publicado
owner: bruno@empresa.com
documento: null               # ainda não escrito em md
artefatos: ["https://waifuvault.moe/f/handoff-fluxo.html"]
relacionadas: [RPQ:pagamentos/reference/modulo-pagamentos]
```

> ⚠ ATRITO 2 — Sem projeto, o `id` não tem sigla para qualificar; aqui ele se
> ancora na **área** dona (`DESIGN:`). O modelo de id para docs sem projeto segue
> em aberto.

---

## Explicação: documentação interna de departamento (padrão recorrente)

```yaml
id: RPQ:explanation/padroes-de-engenharia
slug: padroes-de-engenharia
titulo: "Padrões de Engenharia do TI"
resumo: "Convenções internas de código, branching e revisão do time de TI."
proposito: explicacao
departamento: TI             # dono e mantenedor
publico_alvo: [TI]           # relevância primária = o próprio time
projeto: [RPQ]
module: global
palavras_chave: [convenções, code-review, branching, padrões]
status: publicado
owner: ana@empresa.com
documento: repo://docs/explanation/engineering-standards.md
```

> O padrão que se repete em **todo** departamento: dono = assunto = público primário
> = uma **única** Área. É o caso que justifica `departamento` ser de valor único.
> `publico_alvo: [TI]` sinaliza **relevância**, não acesso — toda Entrada é visível
> para a empresa inteira (o Brainiac é interno).

---

## Só-Artefato, autor não-técnico (Marketing)

```yaml
id: NATAL26:how-to/aprovacao-campanha
slug: processo-aprovacao-campanha
titulo: "Como aprovar uma campanha"
resumo: "Passo a passo visual para aprovar uma campanha antes de publicar."
proposito: how-to
departamento: Marketing
publico_alvo: [Marketing, Negócio]
projeto: [NATAL26]               # em Marketing, o "projeto" é a campanha
palavras_chave: [campanha, aprovacao, publicacao]
status: rascunho
owner: carla@empresa.com
artefatos: ["https://waifuvault.moe/f/aprovacao-campanha.html"]  # só HTML
```

> Valida a invariante "≥1 entre documento/artefatos": aqui só há artefato, e
> a recuperação por IA depende 100% de `resumo` + `palavras_chave` + facetas.

---

## Coleção: Onboarding Dev

```yaml
id: onboarding-dev
slug: onboarding-dev
titulo: "Onboarding Dev"
resumo: "Trilha para um dev novo entender negócio e stack."
publico_alvo: [TI]
status: publicado
owner: ana@empresa.com
entradas:                                       # ORDENADA
  - RPQ:explanation/visao-de-negocio            # pendente (Entrada ainda não existe)
  - RPQ:explanation/arquitetura-de-modulos      # pendente (Entrada ainda não existe)
  - RPQ:pagamentos/reference/modulo-pagamentos
  - DESIGN:how-to/handoff-design-dev
```

> A trilha pode apontar para Entradas que **ainda não existem** (visão de negócio,
> arquitetura): a Coleção é uma "lista de desejos" legítima, e o Brainiac mostra o
> item como **pendente** até a Entrada existir.
