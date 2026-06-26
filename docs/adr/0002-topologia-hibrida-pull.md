# Topologia de documentação: híbrido com federação por PULL

> **Refinamento (ver [federacao.md](../federacao.md)):** como o `/docs` de cada
> repo roda só em DEV e os repos são privados, o mecanismo concreto não é puxar de
> um `/docs` no ar — é **sincronizar o markdown do git para um espelho de leitura**
> no central (disparado por push). O modelo de dois andares e "código não vira
> conteúdo empurrado" continua; muda só o encanamento (mecanismo em aberto: A/B/C).
>
> **Fronteira (ver [ADR-0003](0003-doc-produto-regra-central-spec-repo.md) ·
> [0007](0007-prd-unidade-central-de-produto.md)):** "fonte da verdade" aqui é do
> **código / doc técnica**. O **requisito de produto** nasce no portal central (o
> **PRD**); a spec/ADR do repo é **derivada do PRD** — a direção é sempre PRD → spec.

A documentação tem **dois andares**. As docs de TI ficam **co-localizadas no
repositório** de cada projeto (markdown + front-matter), que é a **fonte da
verdade do código**. Um **portal central** dá a visão única da empresa **federando** as
docs de TI por **PULL** (consulta a API de cada portal de repo, ex.: o
`/api/tree` do laradocs) e **hospeda nativamente** as docs dos departamentos
não-técnicos (Produto, Marketing…). O código **nunca** é empurrado pra fora do
repo.

Consideramos: (a) totalmente central — todo departamento, inclusive TI, sobe doc
(CI empurra a doc do código) — rejeitado por acoplar a doc do código a um sistema
externo e torná-la refém do pipeline; (b) totalmente federado — sem centro,
ligação só por convenção — rejeitado por não dar à liderança/Produto um lugar
único pra navegar; (c) híbrido com PUSH — reservado como fallback se os repos não
forem acessíveis ao central (VPN, nem sempre no ar).

Escolhemos PULL porque: a doc do código continua versionada junto ao código e no
fluxo do dev; não há cópia/sincronização (sem staleness); a liderança ganha uma
porta única; e as ferramentas já existentes (laradocs expõe `/api/tree` e MCP; o
módulo `docs` do he4rt faz auto-discovery) já entregam a metade da federação.

Consequências: (1) o central depende dos repos/portais estarem acessíveis no
momento da consulta — daí o fallback PUSH existir; (2) duas coisas que **nenhuma**
ferramenta pesquisada (laradocs, módulo he4rt) entrega precisam ser construídas:
o **hosting com input web para não-técnicos** e o **grafo de links
produto↔código**.
