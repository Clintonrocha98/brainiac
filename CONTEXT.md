# Documentação da Empresa

Sistema de documentação cross-departamento da empresa: um catálogo único onde
cada documento/artefato vira uma entrada com metadados, navegável por humanos e
recuperável por IA. Este arquivo é o glossário — só linguagem, sem detalhes de
implementação.

## Language

**Documento**:
O conteúdo-fonte em markdown produzido pelas skills (brainstorm/grill-me) que
origina um Artefato.
_Avoid_: doc, texto, markdown

**Artefato**:
A página renderizável (HTML/CSS/JS, normalmente Tailwind) gerada a partir de um
Documento; é o formato que as pessoas compartilham entre si.
_Avoid_: página, anexo, build

**Catálogo**:
O índice federado dentro do Brainiac: a lista de todas as Entradas (as do
próprio Brainiac + as espelhadas dos repos) com seus metadados. Para as Entradas
de TI guarda metadado + conteúdo renderizado (espelho) + ponteiro de volta à
origem no git; para as nativas (Produto, Marketing…), o conteúdo mora ali mesmo.
_Avoid_: listagem, biblioteca, repositório, acervo

**Entrada**:
Um item do Catálogo — a referência a um Documento/Artefato somada aos seus
metadados de classificação.
_Avoid_: registro, card, item, verbete

**Metadado**:
Os campos estruturados que classificam uma Entrada para navegação humana e
recuperação por IA.
_Avoid_: tag (tag é um tipo específico de metadado), atributo

**Faceta**:
Um Metadado usado para filtrar e recuperar Entradas dentro de um Propósito
(ex.: departamento, audiência). É o que substitui a "pasta por departamento".
_Avoid_: filtro, dimensão, categoria

**Coleção**:
Uma view curada que agrupa várias Entradas já existentes para um público ou
objetivo (ex.: a trilha de onboarding). Não é um Propósito e não contém conteúdo
próprio — apenas aponta para Entradas. Handbook é uma Coleção específica.
_Avoid_: trilha, pasta, handbook, acervo

**Área**:
O conjunto controlado de departamentos/times da empresa; é o vocabulário
compartilhado pelas facetas `departamento` e `publico_alvo`. Valores atuais:
`TI`, `Negócio`, `Produto`, `Marketing`, `Design` (todas separadas — Negócio não
é guarda-chuva; Design não vive dentro de Produto). `publico_alvo` admite ainda
`todos` e `externo`. Lista fechada, extensível só via governança.
_Avoid_: setor, time, squad

**Projeto**:
Entidade de 1ª classe registrada no Brainiac, com `nome_negocio`, `nome_tecnico`,
`slug` e `sigla`. É o contêiner dos documentos e a "origem" da federação. Resolve
o desalinhamento de nomes (negócio × TI) sendo a camada de tradução.
Ver [[0006-projeto-primeira-classe-sigla-canonica]].
_Avoid_: sistema, repo, produto

**Sigla**:
O handle canônico de uma Projeto (ex.: `RPQ`). Alinha negócio ↔ TI ↔ rastreador ↔
catálogo; é a "origem" dos ids qualificados (`RPQ:adr/0001`) e o prefixo do
rastreador (`RPQ-STORY-123`).
_Avoid_: código, acrônimo, prefixo

**projeto** (faceta):
A faceta que diz a qual Projeto a Entrada pertence — referencia uma Projeto pela
`sigla`. Multi-valor e opcional.
_Avoid_: sistema, módulo, repo, produto

**Vocabulário controlado**:
A regra de que as facetas (`tipo`, `departamento`, `publico_alvo`, `projeto`)
só aceitam valores de uma lista fechada — nunca texto livre.
Apenas `palavras_chave` é livre. Existe para não quebrar filtro e recuperação
por IA.
_Avoid_: enum, lista, taxonomia fechada

**id** (campo):
O identificador canônico e estável da Entrada. Não há id global cunhado pelo
catálogo: cada origem é dona do seu id nativo e o catálogo apenas **qualifica com
a sigla** do Projeto (ex.: `RPQ:adr/0001` (global), `RPQ:pagamentos/adr/0001` (de
módulo), `RPQ:PRD-12`). Nunca muda, mesmo que
título ou facetas mudem; é o que relacionamentos e links referenciam.
Ver [[0006-projeto-primeira-classe-sigla-canonica]].
_Avoid_: código, número, DOC-NNNN, slug (slug é outra coisa)

**slug** (campo):
A parte legível e cosmética da URL de uma Entrada (ex.: `setup-ambiente`). Pode
mudar livremente sem quebrar o `id`.
_Avoid_: id, permalink

**resumo** (campo):
Uma a três frases que descrevem a Entrada; serve ao mesmo tempo de preview para
humano e de sinal textual para a recuperação por IA.
_Avoid_: descrição, ementa, abstract

**palavras_chave** (campo):
O único campo de texto livre da Entrada — uma lista de termos que serve tanto
para agrupar quanto para recuperar. Substitui a ideia de `tags` (não há campo
`tags` separado).
_Avoid_: tags, keywords, rótulos

**status** (campo):
O estado de ciclo de vida da Entrada: `rascunho`, `revisão`, `publicado` ou
`obsoleto`. É um **sinal social**, não uma trava — a plataforma não impõe
aprovação (ver [[0008-governanca-do-prd-social-por-status]]). Em `revisão` o
documento já é legível; em `publicado` passa a valer como a versão corrente/oficial
daquela Entrada (o PRD, do produto; a spec, da implementação).
_Avoid_: estado, situação

**departamento** (faceta):
A Área que produz e mantém a Entrada — o dono. Uma só por Entrada.
_Avoid_: time, dono, autor

**publico_alvo** (faceta):
As Áreas/perfis para quem a Entrada se destina — o leitor. Pode ser multi-valor.
_Avoid_: audiência, destinatário, leitor

## Propósitos

O Propósito é o tipo de conhecimento que um Documento entrega — o eixo de topo da
taxonomia. São cinco e são mutuamente exclusivos.
_Avoid_ (para "Propósito"): tipo, categoria, formato

**Referência**:
Fatos consultáveis pontualmente: API, esquema de um módulo, glossário, spec.
Você consulta, não lê de ponta a ponta.

**How-to**:
Passos para realizar uma tarefa específica. Você lê para executar.
_Avoid_: tutorial, guia, passo-a-passo

**Explicação**:
Entendimento e contexto: regra de negócio, visão de arquitetura, o "porquê".
Você lê para entender.
_Avoid_: documentação técnica, overview

**Decisão**:
Registro de uma decisão e seu trade-off (um ADR). Você lê para saber por que
foi feito assim.
_Avoid_: ADR (ADR é o formato; Decisão é o propósito)

**Processo**:
O fluxo de trabalho recorrente de uma área — inclui handoffs entre times/fases.
Você lê para seguir um rito.
_Avoid_: handoff, fluxo, SOP, rito

## Andares e fluxo de produto

**Brainiac**:
A plataforma central de documentação da empresa — o "portal central" que antes não
tinha nome. É o andar empresa: é onde **nasce o PRD** (fonte da verdade do produto),
**federa** (recebe por PUSH do módulo de doc) e espelha as docs de TI, **hospeda**
nativamente as docs não-técnicas e é a **porta única** para liderança e Produto.
Cada repo de TI continua dono da sua doc técnica; o Brainiac unifica o acesso.
Ver [[0002-topologia-hibrida-pull]], [[0009-federacao-por-push-modulo]].
_Avoid_: portal central, portal, central, wiki, hub

**Federação**:
O módulo de doc de cada repo **empurra** (PUSH, via o comando `docs:publish`) um
snapshot da doc de TI para um **espelho de leitura** no Brainiac (metadado indexado
+ conteúdo renderizado). O git continua a fonte da verdade do código; o Brainiac é a
superfície de leitura em produção — os repos são privados e o `/docs` roda só em DEV,
então não há de onde puxar ao vivo.
Ver [[0002-topologia-hibrida-pull]], [[0009-federacao-por-push-modulo]].
_Avoid_: agregação, importação, índice remoto

**PRD**:
Documento de requisitos de produto que vive no Brainiac; dono é Produto;
versionado (última versão = fonte da verdade). Grão de uma feature ou grupo coeso
de features — nunca o projeto inteiro. Contém as regras de negócio como seção
interna. Major = muda comportamento (gera Spec); minor = ajuste de texto.
Ver [[0003-doc-produto-regra-central-spec-repo]], [[0007-prd-unidade-central-de-produto]].
_Avoid_: Regra (nome antigo do tipo), requisito

**regra de negócio**:
Uma afirmação normativa que o sistema deve obedecer (ex.: "voucher é de uso
único"). **Não** é um tipo de documento — é uma seção dentro de um PRD.
_Avoid_: Regra (maiúsculo, como se fosse tipo), política

**Visão de produto**:
Documento macro (explanation, evergreen) que descreve o produto como um todo —
objetivo, escopo, personas, roadmap. Uma por Projeto, acima dos PRDs.
_Avoid_: PRD (PRD é por feature), overview

**Spec**:
Documento datado e imutável, co-localizado no repo, que registra como uma versão
do PRD foi implementada. Escrita por TI (via grill-me-with-docs); referencia a
versão do PRD pelo id.
_Avoid_: especificação, implementação

**Rastreador**:
A ferramenta onde as tasks vivem (hoje Monday). É projeção da documentação, não
fonte da verdade; suas tasks carregam o id do PRD. Intercambiável.
Ver [[0004-doc-upstream-do-rastreador]].
_Avoid_: Monday, board, gestor de tarefas

## Tipos e metadados

**Tipo** (de documento):
O dicionário compartilhado que classifica toda Entrada, igual em todos os
departamentos (how-to é how-to em qualquer área). Cada Tipo é evergreen ou
datado. Substitui e refina o antigo "Propósito".
_Avoid_: propósito, categoria, formato

**Evergreen** (classe de Tipo):
Tipo cujo documento é **editado** para refletir o estado atual; existe um por
assunto (README, CONTEXT, reference, how-to, explanation; a Regra é evergreen
versionada).
_Avoid_: vivo, atual

**Datado** (classe de Tipo):
Tipo cujo documento é **congelado** num momento e nunca editado; cria-se um novo
a cada vez (ADR, spec, plan). Leem-se em conjunto.
_Avoid_: append-only, imutável, histórico

**Metadado core**:
Os Metadados que toda Entrada carrega, em qualquer departamento ou tipo (id,
titulo, resumo, tipo, departamento, publico_alvo, status, owner, datas,
palavras_chave, related). A base compartilhada.
_Avoid_: campos base, padrão

**Extensão de departamento**:
Bloco de Metadados que só faz sentido para um departamento (ex.: `module` no TI,
`canal` no Marketing, `segmento` no Produto), sem poluir as Entradas das outras
áreas. Há também metadados **por tipo** (ex.: `deciders` no ADR, `versao` no PRD).
_Avoid_: campo custom, metadado extra

**module** (extensão de TI):
A parte do sistema de que uma Entrada de TI fala — o seu **escopo**. Recebe o nome
do módulo (ex.: `pagamentos`) ou o valor reservado `global` quando a doc é do
projeto inteiro. Obrigatório no TI; distingue o README/ADR/spec **de um módulo** do
**global** e permite filtrar por módulo.
_Avoid_: escopo, pacote, sistema

**Guideline de autoria**:
O prompt versionado que **qualquer autor** (TI ou não-técnico) usa via IA para
gerar um Documento + front-matter já no vocabulário controlado, sem preencher
campos à mão — a IA preenche o front-matter; o ingest é determinístico. No TI é a
skill grill-me-with-docs; fora dele, a guideline colada no Claude web. Não confundir
com as guidelines técnicas do repo (convenções de código).
Ver [[0005-autoria-nao-tecnico-guideline-paste]].
_Avoid_: prompt, template, stub
