# Federação na prática — como a doc de TI chega ao central

> **Status: EM ABERTO** (mecanismo a decidir). Recomendação: **Opção A**.
> Refina o [ADR-0002](adr/0002-topologia-hibrida-pull.md).

## Contexto (o que mudou)

Descobrimos dois fatos que mudam o mecanismo da federação:

- O `/docs` de cada repo (módulo estilo he4rt) roda **só em DEV** — é preview do
  dev; **não vai pra produção**.
- Os repos são **privados**.

Consequência: o central **não puxa de um `/docs` publicado** (não há um no ar). A
fonte é o **markdown no git** (sempre disponível, via PR). E como os repos são
privados e não há `/docs` em prod, o central precisa **espelhar** (guardar
metadado + conteúdo renderizado) para a liderança ler — ele vira a **superfície de
leitura de produção**; o git continua a **fonte da verdade**.

```
FONTE DA VERDADE                         CENTRAL  (superfície de leitura em PROD)
repo GitHub (privado)
  docs/**.md + front-matter  ──sync──►   ESPELHO:
  (sempre no git, via PR)                  • metadado indexado (busca/filtro)
  /docs (rota) = só DEV                    • conteúdo renderizado (liderança lê)
  (preview do dev; não vai p/ prod)        • grafo de links (PRD ↔ spec)
```

> "Federar" aqui é **sincronizar do git para um espelho de leitura**, não puxar de
> um `/docs` no ar. O central renderiza o markdown ele mesmo (o renderizador do
> módulo he4rt é reaproveitável).

## As 3 opções de mecanismo de sync

### A) Central puxa via GitHub API + webhook — **recomendada**

```
repo: push na main ──webhook──► CENTRAL
                                  └─ lê docs/**.md via GitHub API (App/token)
                                     indexa · renderiza · espelha
```

- **Webhook**: GitHub liga numa URL do central a cada push ("repo X mudou").
- **GitHub App/token**: credencial que deixa o central ler arquivos dos repos
  privados pela API.
- **+** zero step por repo · sync automático · TI só commita · sem mexer em CI ·
  dispensa "guideline pra avisar o portal" (o push já dispara).
- **−** o central precisa de um token de leitura dos repos (instala uma vez na org).

### B) CI do repo faz PUSH no merge

```
repo: merge ─► CI step "publicar docs" ─► POST /ingest ─► CENTRAL
```

- **+** o repo controla o publish · o central não precisa de chave dos repos.
- **−** exige configurar um step de CI em cada repo.

### C) Projeto expõe uma API de docs e o central consome

```
app do projeto (publicado) ─ GET /api/docs ─► CENTRAL
```

- **−** exige o app do projeto **publicado e acessível** — **conflita** com
  "`/docs` só em dev". Só vível se publicar o `/docs` em prod.

## O que já está firme vs. o que fica aberto

- **Firme (design):** o central **espelha** a doc, **disparado por um push no
  git** (em vez de exigir o app do projeto no ar).
- **Aberto (implementação):** o encanamento exato — webhook+API (A) vs CI push
  (B). A fechar na hora de construir.
