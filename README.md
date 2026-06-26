# 🧠 Brainiac

> Sistema de documentação da empresa — uma casa **única, federada e amigável à
> IA** para a documentação técnica (TI) e de negócio (Produto, Marketing,
> Negócio), pensada para incluir também quem **não é técnico**.

## 🎨 Apresentação visual

**[🔗 Abrir a apresentação visual](https://waifuvault.moe/f/59d704b6-a471-4487-b3c7-1ec982d631b0/2026-06-26-brainiac-arquitetura.html)**
— overview interativo (abas + diagramas) do desenho até aqui, para tech lead / liderança.

## 👉 Por onde começar

**[`docs/arquitetura.md`](docs/arquitetura.md)** — o overview consolidado, com os
diagramas de topologia, fluxo PRD ↔ Spec, autoria e governança. É a porta de
entrada para entender o desenho inteiro.

Para a linguagem/termos: **[`CONTEXT.md`](CONTEXT.md)** (glossário canônico).

## O que é o Brainiac

A empresa cresceu e a documentação não acompanhou: TI tem só READMEs técnicos por
módulo, os artefatos vivem espalhados, e os times de negócio (Produto, Marketing)
não têm onde nem como documentar. O Brainiac é a **infraestrutura** que resolve
isso — não "escrever mais doc", mas dar um **modelo único** onde cada documento é
classificado, achável por humano **e por IA**, independente do departamento.

A ideia central é de **dois andares**:

- **Empresa (portal central):** é onde **nasce o PRD** — a fonte da verdade do
  *produto*. Federa/espelha a doc de TI e **hospeda** a doc dos times
  não-técnicos, sendo a porta única para liderança e Produto.
- **TI (no repo):** a doc **técnica** vive co-localizada no código (markdown +
  front-matter) — fonte da verdade do *código*. A **spec/ADR** de cada entrega é
  **derivada do PRD**, não o contrário.

## Pilares (resumo)

| Pilar | Em uma linha |
|---|---|
| **Taxonomia** | Organiza por **tipo/propósito**, não por departamento (departamento é faceta) |
| **Topologia** | Dois andares: produto nasce no central (verdade do PRD) + TI no repo (verdade do código) |
| **Produto** | **PRD** versionado no central (grão de feature; regras dentro) + Spec datada no repo |
| **Identidade** | Doc é upstream do rastreador (Monday é projeção); `Projeto`/sigla (`RPQ`) cola tudo |
| **Autoria** | Não-técnico escreve por **IA** (guideline → colar); portal determinístico |
| **Governança** | `status` é sinal social (rascunho → revisão → publicado), sem gate rígido |

## Mapa dos documentos

- **[`CONTEXT.md`](CONTEXT.md)** — glossário: a linguagem canônica do projeto.
- **[`docs/arquitetura.md`](docs/arquitetura.md)** — overview consolidado (comece aqui).
- **[`docs/tipos-ti.md`](docs/tipos-ti.md)** — os 8 tipos de TI (evergreen × datado).
- **[`docs/federacao.md`](docs/federacao.md)** — como a doc de TI chega ao central (3 opções).
- **[`docs/taxonomia.md`](docs/taxonomia.md)** — schema e facetas de metadados (sessão 1).
- **[`docs/exemplos-entradas.md`](docs/exemplos-entradas.md)** — casos reais que estressaram o schema.

### Decisões (ADRs) — [`docs/adr/`](docs/adr/)

| # | Decisão |
|---|---|
| [0001](docs/adr/0001-taxonomia-orientada-a-proposito.md) | Taxonomia orientada a propósito, não a departamento |
| [0002](docs/adr/0002-topologia-hibrida-pull.md) | Topologia híbrida (TI no repo, central federa) |
| [0003](docs/adr/0003-doc-produto-regra-central-spec-repo.md) | Doc de produto: requisito no central + Spec no repo |
| [0004](docs/adr/0004-doc-upstream-do-rastreador.md) | Documentação é upstream do rastreador (Monday é projeção) |
| [0005](docs/adr/0005-autoria-nao-tecnico-guideline-paste.md) | Autoria do não-técnico: guideline → colar (v1), chat (v2) |
| [0006](docs/adr/0006-projeto-primeira-classe-sigla-canonica.md) | Projeto é 1ª classe; a sigla é o handle canônico |
| [0007](docs/adr/0007-prd-unidade-central-de-produto.md) | PRD é a unidade central de produto (grão de feature) |
| [0008](docs/adr/0008-governanca-do-prd-social-por-status.md) | Governança do PRD: status social, sem gate |

## Status

Fase de **desenho** (design via grilling) — ainda **não há código**.

**Pontos abertos:** mecanismo de federação (recomendado: GitHub API + webhook) ·
tipos de Marketing e Negócio · tecnologia do portal (laradocs × evoluir o módulo
he4rt × algo novo).
