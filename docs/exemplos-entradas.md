# Exemplos de Entradas (validação do schema)

Casos escolhidos para estressar o schema. `⚠ ATRITO` marca onde o preenchimento
doeu. Dados são placeholders realistas — o que importa aqui é a *forma*.

---

## DOC-0001 — Referência: módulo de Pagamentos

```yaml
id: DOC-0001
slug: modulo-pagamentos
titulo: "Módulo de Pagamentos — referência técnica"
resumo: "Contratos, eventos e configuração do módulo de Pagamentos."
proposito: referencia
departamento: TI
publico_alvo: [TI]
projeto: [pagamentos]
palavras_chave: [pagamentos, gateway, webhook, idempotencia]
status: publicado
owner: ana@empresa.com
documento: repo://modules/payments/README.md
artefatos: []
relacionadas: [DOC-0002]
```

> ⚠ ATRITO 1 — O README real do módulo mistura **referência** (contratos),
> **how-to** (como rodar local) e **explicação** (por que idempotência). Mas
> `proposito` é valor único (MECE). Esse README não é UMA Entrada — são três.

---

## DOC-0002 — Processo: handoff Design → Dev

```yaml
id: DOC-0002
slug: handoff-design-dev
titulo: "Handoff de Design para Desenvolvimento"
resumo: "Rito de passagem de uma tela do Figma aprovado para a fila de dev."
proposito: processo
departamento: Design          # dono do rito
publico_alvo: [Design, TI]    # bilateral — os dois lados seguem
projeto: []                   # ⚠ ATRITO 2 — processo não é "de um projeto"
palavras_chave: [handoff, figma, design-system, definicao-de-pronto]
status: publicado
owner: bruno@empresa.com
documento: null               # ainda não escrito em md
artefatos: ["https://waifuvault.moe/f/handoff-fluxo.html"]
relacionadas: [DOC-0001]
```

---

## DOC-0003 — só-Artefato, autor não-técnico (Marketing)

```yaml
id: DOC-0003
slug: processo-aprovacao-campanha
titulo: "Como aprovar uma campanha"
resumo: "Passo a passo visual para aprovar uma campanha antes de publicar."
proposito: how-to
departamento: Marketing
publico_alvo: [Marketing, Negócio]
projeto: [campanha-natal-2026]   # ⚠ ATRITO 3 — "projeto" aqui é campanha
palavras_chave: [campanha, aprovacao, publicacao]
status: rascunho
owner: carla@empresa.com
documento: null
artefatos: ["https://waifuvault.moe/f/aprovacao-campanha.html"]  # só HTML
```

> Valida a invariante "≥1 entre documento/artefatos": aqui só há artefato, e
> a recuperação por IA depende 100% de `resumo` + `palavras_chave` + facetas.

---

## COL-0001 — Coleção: Onboarding Dev

```yaml
id: COL-0001
slug: onboarding-dev
titulo: "Onboarding Dev"
descricao: "Trilha para um dev novo entender negócio e stack."  # ⚠ ATRITO 5
publico_alvo: [TI]
status: publicado
owner: ana@empresa.com
entradas:                     # ORDENADA
  - DOC-0010   # Explicação: visão de negócio (não existe ainda)
  - DOC-0011   # Explicação: arquitetura de módulos (não existe ainda)
  - DOC-0001   # Referência: módulo de Pagamentos
  - DOC-0002   # Processo: handoff
```

> ⚠ ATRITO 6 — a trilha de onboarding aponta pra Entradas que **ainda não
> existem** (visão de negócio, arquitetura). Coleção como "lista de desejos"
> também é um uso legítimo? Ou só aponta pra Entradas publicadas?
