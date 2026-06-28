# Topologia de documentação: híbrido com federação por PULL

> **Transporte revisado (ver [ADR-0009](0009-federacao-por-push-modulo.md) ·
> [federacao.md](../federacao.md)):** o mecanismo concreto **não é PULL** — é
> **PUSH pelo módulo de doc**: um comando `docs:publish` empurra um snapshot do
> markdown para um webhook do Brainiac, que **espelha** (os repos são privados e o
> `/docs` roda só em DEV, então não há de onde puxar ao vivo). O modelo de dois
> andares e "só a doc sai, o código nunca sai" continua; muda só o transporte
> (PULL → PUSH).
>
> **Fronteira (ver [ADR-0003](0003-doc-produto-regra-central-spec-repo.md) ·
> [0007](0007-prd-unidade-central-de-produto.md)):** "fonte da verdade" aqui é do
> **código / doc técnica**. O **requisito de produto** nasce no Brainiac (o
> **PRD**); a spec/ADR do repo é **derivada do PRD** — a direção é sempre PRD → spec.

A documentação tem **dois andares**. As docs de TI ficam **co-localizadas no
repositório** de cada projeto (markdown + front-matter), que é a **fonte da
verdade do código**. O **Brainiac** dá a visão única da empresa **federando** as
docs de TI por **PULL** (consulta a API de cada portal de repo, ex.: o
`/api/tree` do laradocs) e **hospeda nativamente** as docs dos departamentos
não-técnicos (Produto, Marketing…). O código **nunca** é empurrado pra fora do
repo.

Consideramos: (a) totalmente central — todo departamento, inclusive TI, sobe doc
(CI empurra a doc do código) — rejeitado por acoplar a doc do código a um sistema
externo e torná-la refém do pipeline; (b) totalmente federado — sem centro,
ligação só por convenção — rejeitado por não dar à liderança/Produto um lugar
único pra navegar; (c) híbrido com PUSH — reservado como fallback se os repos não
forem acessíveis ao Brainiac (VPN, nem sempre no ar).

Escolhemos PULL porque: a doc do código continua versionada junto ao código e no
fluxo do dev; não há cópia/sincronização (sem staleness); a liderança ganha uma
porta única; e as ferramentas já existentes (laradocs expõe `/api/tree` e MCP; o
módulo `docs` do he4rt faz auto-discovery) já entregam a metade da federação.

Consequências: (1) o Brainiac depende dos repos/portais estarem acessíveis no
momento da consulta — daí o fallback PUSH existir; (2) duas coisas que **nenhuma**
ferramenta pesquisada (laradocs, módulo he4rt) entrega precisam ser construídas:
o **hosting com input web para não-técnicos** e o **grafo de links
produto↔código**.
