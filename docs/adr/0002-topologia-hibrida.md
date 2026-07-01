# Topologia de documentação: híbrida — TI no repo, Brainiac federa

A documentação tem **dois andares**. As docs de TI ficam **co-localizadas no
repositório** de cada projeto (markdown + front-matter), que é a **fonte da
verdade do código**. O **Brainiac** dá a visão única da empresa: **federa** as
docs de TI e **hospeda nativamente** as docs dos departamentos não-técnicos
(Produto, Marketing…). O código **nunca** sai do repo — só a doc.

O **requisito de produto** nasce no Brainiac (o **PRD**, fonte da verdade do
produto); a **spec/ADR** do repo é **derivada do PRD** — a direção é sempre
PRD → spec, nunca o contrário (ver
[Documentação de produto: PRD no Brainiac, Spec no repo](0003-doc-produto-prd-spec-repo.md) ·
[PRD é a unidade central de produto](0007-prd-unidade-central-de-produto.md)). A federação acontece por
**push do módulo de doc** (ver [Federação por PUSH pelo módulo](0009-federacao-por-push-modulo.md)): os
repos são privados e o `/docs` roda só em DEV, então é o próprio módulo que empurra
um snapshot para o Brainiac, que espelha.

Consideramos três topologias: (a) **totalmente central** — todo departamento,
inclusive TI, sobe doc num sistema externo — rejeitada por acoplar a doc do código
a um sistema externo e torná-la refém dele; (b) **totalmente federada** — sem
centro, ligação só por convenção — rejeitada por não dar à liderança/Produto um
lugar único pra navegar; (c) **híbrida** (esta) — TI no repo + um centro que
federa e hospeda o resto.

Escolhemos a híbrida porque a doc do código continua versionada junto ao código e
no fluxo do dev, e, ao mesmo tempo, a liderança e o Produto ganham uma porta única
para navegar.

Consequência: duas coisas precisam ser construídas (nenhuma ferramenta de mercado
entrega prontas) — o **hosting com input web para não-técnicos** e o **grafo de
links produto↔código**.
