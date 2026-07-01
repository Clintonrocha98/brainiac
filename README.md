# 🧠 Brainiac

> Sistema de documentação da empresa — uma casa **única, federada e amigável à
> IA** para a documentação técnica (TI) e de negócio (Produto, Marketing,
> Negócio), pensada para incluir também quem **não é técnico**.

## 🎨 Apresentação visual

**[🔗 Abrir a apresentação visual](https://waifuvault.moe/f/4d11a341-76d5-4ba9-b703-d3139bb2a6e8/brainiac-artefato.html)**
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

- **Empresa — o Brainiac (a plataforma central):** é onde **nasce o PRD** — a fonte
  da verdade do *produto*. Federa/espelha a doc de TI e **hospeda** a doc dos times
  não-técnicos, sendo a porta única para liderança e Produto.
- **TI (no repo):** a doc **técnica** vive co-localizada no código (markdown +
  front-matter) — fonte da verdade do *código*. A **spec/ADR** de cada entrega é
  **derivada do PRD**, não o contrário.

## Pilares (resumo)

| Pilar | Em uma linha |
|---|---|
| **Taxonomia** | Organiza por **propósito**, não por departamento (departamento é faceta) |
| **Topologia** | Dois andares: produto nasce no Brainiac (verdade do PRD) + TI no repo (verdade do código) |
| **Produto** | **PRD** versionado no Brainiac (grão de feature; regras dentro) + Spec datada no repo |
| **Identidade** | Doc é upstream do rastreador (Monday é projeção); `Projeto`/sigla (`RPQ`) cola tudo |
| **Autoria** | **Todo autor** escreve por **IA** (a guideline preenche o front-matter); Brainiac determinístico |
| **Governança** | `status` é sinal social (rascunho → revisão → publicado), sem gate rígido |

## Mapa dos documentos

- **[`CONTEXT.md`](CONTEXT.md)** — glossário: a linguagem canônica do projeto.
- **[`docs/arquitetura.md`](docs/arquitetura.md)** — overview consolidado (comece aqui).
- **[`docs/formatos-ti.md`](docs/formatos-ti.md)** — os 8 formatos de TI (evergreen × datado).
- **[`docs/federacao.md`](docs/federacao.md)** — como a doc de TI chega ao Brainiac (push pelo módulo).
- **[`docs/taxonomia.md`](docs/taxonomia.md)** — schema e facetas de metadados.
- **[`docs/exemplos-entradas.md`](docs/exemplos-entradas.md)** — casos reais que estressaram o schema.
- **[`docs/pesquisa/`](docs/pesquisa/)** — pesquisa de apoio: confronto do desenho com o estado da arte (insumo, não decisão).

### Decisões (ADRs) — [`docs/adr/`](docs/adr/)

| # | Decisão |
|---|---|
| [0001](docs/adr/0001-taxonomia-orientada-a-proposito.md) | Taxonomia orientada a propósito, não a departamento |
| [0002](docs/adr/0002-topologia-hibrida.md) | Topologia híbrida (TI no repo, Brainiac federa) |
| [0003](docs/adr/0003-doc-produto-prd-spec-repo.md) | Doc de produto: requisito no Brainiac + Spec no repo |
| [0004](docs/adr/0004-doc-upstream-do-rastreador.md) | Documentação é upstream do rastreador (Monday é projeção) |
| [0005](docs/adr/0005-autoria-nao-tecnico-guideline-paste.md) | Autoria do não-técnico: guideline → colar (v1), chat (v2) |
| [0006](docs/adr/0006-projeto-primeira-classe-sigla-canonica.md) | Projeto é 1ª classe; a sigla é o handle canônico |
| [0007](docs/adr/0007-prd-unidade-central-de-produto.md) | PRD é a unidade central de produto (grão de feature) |
| [0008](docs/adr/0008-governanca-do-prd-social-por-status.md) | Governança do PRD: status social, sem gate |
| [0009](docs/adr/0009-federacao-por-push-modulo.md) | Federação por PUSH: o módulo publica no Brainiac (`docs:publish`) |
| [0010](docs/adr/0010-markdown-canonico-render-centralizado.md) | Markdown é o formato canônico; o render é centralizado no Brainiac |
| [0011](docs/adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md) | Ciclo de vida do PRD: o texto versiona e congela ao publicar |
| [0012](docs/adr/0012-artefato-asset-html-por-link-iframe-isolado.md) | Artefato: asset HTML por link, embutido em iframe isolado |

## Status

Fase de **desenho** (design via grilling) — ainda **não há código**.

**Stack do Brainiac:** Laravel + Filament (autoria do PRD + edição do subconjunto
editável de metadado) + Livewire (vitrine de leitura) + `commonmark`/`highlight`
(render). Conteúdo canônico em **markdown**, renderizado pelo Brainiac (ver [Markdown canônico, render centralizado](docs/adr/0010-markdown-canonico-render-centralizado.md)).
Recuperação por IA = **API com filtros** sobre o vocabulário controlado.

**Pontos abertos:** propósitos e formatos de Marketing e Negócio · v2 do chat de autoria · **busca
humana** (adiada; default Postgres FTS + filtros). *(Auth e infra/hosting adiados.)*
