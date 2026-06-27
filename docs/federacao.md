# Federação na prática — como a doc de TI chega ao Brainiac

> **Status: DECIDIDO** — federação por **PUSH pelo módulo** (opção D).
> Ver [ADR-0009](adr/0009-federacao-por-push-modulo.md). Refina o transporte do
> [ADR-0002](adr/0002-topologia-hibrida-pull.md) (era PULL).
>
> **Render ([ADR-0010](adr/0010-markdown-canonico-render-centralizado.md)):** o
> publicador **não renderiza** — manda **markdown + metadado**; quem renderiza é o
> Brainiac (markdown é o formato canônico).

## Contexto (o que descartou o PULL)

Três fatos mataram o PULL original:

- O `/docs` de cada repo (módulo estilo he4rt) roda **só em DEV** — é preview do
  dev; **não vai pra produção**.
- Os repos são **privados**.
- PULL exigiria um **token do GitHub org-wide** (que lê todo o código) **ou** o
  **Brainiac alcançar cada app de prod** (rede privada/VPN) + um **registro das
  rotas** de todos os projetos.

A saída foi **inverter a seta**: o **módulo de documentação** (que vive em todo
projeto) **empurra** a doc para o Brainiac. O git continua a **fonte da verdade**
do código; o Brainiac é a **superfície de leitura em produção** (espelho).

## Como funciona (opção D — push pelo módulo)

```
  PROJETO (módulo de doc — vive em todos os projetos)
  ┌─────────────────────────────────────┐
  │ docs/**.md  (sobe junto com o código)│
  │      │                               │
  │      ▼  comando `docs:publish`       │
  │   lê + valida o front-matter         │
  │   monta SNAPSHOT (markdown + meta)   │
  └──────────────┬──────────────────────┘
                 │ POST (token + HMAC)   ◄── OUTBOUND (app → Brainiac)
                 ▼
       ┌────────────────────────────┐
       │ BRAINIAC  /webhook/ingest   │  ◄── 1 URL pública, só ela
       │  autentica · valida schema  │
       │  renderiza · espelha md+meta│
       │  guarda ponteiro p/ o git   │
       └────────────────────────────┘
```

1. Alguém roda `docs:publish` a partir de um `main` limpo (o comando **se recusa**
   fora do main).
2. O módulo lê `docs/**.md`, valida o front-matter e monta um **snapshot completo**
   (markdown + metadado — **sem renderizar**; ver [ADR-0010](adr/0010-markdown-canonico-render-centralizado.md)).
3. `POST` para o **único webhook de entrada** do Brainiac, com **token do projeto +
   assinatura HMAC**.
4. O Brainiac autentica, valida o schema, **renderiza** e **espelha** (metadado +
   markdown-fonte), guardando o ponteiro de volta ao git.

## O que isso elimina

- **Sem token do GitHub** — o módulo lê o próprio disco.
- **Sem CI** — não há step de pipeline; quem publica é o comando.
- **Sem registro de rotas** — o app se anuncia no payload (traz a `sigla`); o
  Brainiac só precisa de **uma** URL pública.
- **Sem Brainiac → app** — a chamada é **outbound** (app → Brainiac), que atravessa
  rede privada/firewall com facilidade.
- **Sem poll.**

## Decisões de mecanismo (fechadas)

- **Gatilho:** manual/explícito — casa com o `status` social ([ADR-0008](adr/0008-governanca-do-prd-social-por-status.md)):
  publicar é ato deliberado.
- **Origem:** roda do `main` atualizado, com **guarda** → publica o **estado
  merjado** (sem esperar deploy; não precisa de acesso a prod).
- **Snapshot completo** a cada publish → idempotente; **deleção propaga**.
- **Auth:** token por projeto + HMAC.
- **Anti-stale:** como nada dispara sozinho, o Brainiac mostra **"última
  sincronização: há X dias"** por projeto, deixando a defasagem visível.
- **Status não filtra sync:** envia tudo; o badge de cada doc viaja junto e o
  Brainiac exibe.

## Por que não A/B/C

Resumo em [ADR-0009](adr/0009-federacao-por-push-modulo.md): **A** (token org-wide +
lógica de doc duplicada no Brainiac), **B** (refém da saúde do CI + workflow por
repo), **C** (Brainiac precisa alcançar cada app de prod + registro de rotas).
