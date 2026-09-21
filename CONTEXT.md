# Documentação da Empresa

Sistema de documentação cross-departamento da empresa: um catálogo único onde
cada documento vira uma ficha com metadados e um texto versionado, navegável por
humanos e recuperável por IA. Este arquivo é o glossário — só linguagem, sem
detalhes de implementação.

## Language

**Documento**:
Um item do Catálogo: a **ficha** de metadados (id, título, resumo, propósito,
formato, departamento, público-alvo, status, owner) somada ao seu texto, que existe
em uma ou mais Versões. A ficha é uma só e sempre se edita no lugar; o texto é o que
versiona. Um PRD é um Documento com `formato: PRD`, não uma coisa à parte.
Ver [Documento é a ficha, Versão é o conteúdo](docs/adr/0016-documento-e-versao-ficha-e-conteudo.md).
_Avoid_: entrada, registro, card, item, verbete, doc, texto

**Versão**:
Um estado do **texto** (corpo markdown) de um Documento. Todo Documento tem pelo
menos uma; um Documento nativo pode acumular várias; um espelho tem exatamente uma,
substituída a cada sincronização. Só o texto versiona — metadado (fora `status`)
edita-se no lugar. No PRD, a Versão **congela ao publicar** e ganha número
(`RPQ:PRD-12@v2.0`); a última publicada é a verdade corrente, as anteriores são
histórico.
Ver [Documento é a ficha, Versão é o conteúdo](docs/adr/0016-documento-e-versao-ficha-e-conteudo.md), [Ciclo de vida do PRD](docs/adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md).
_Avoid_: revisão (revisão é um `status`), edição, corpo, body, markdown

**Catálogo**:
O índice federado dentro do Brainiac: a lista de todos os Documentos (os do
próprio Brainiac + os espelhados dos repos) com seus metadados. Para os Documentos
de TI guarda metadado + o markdown-fonte (espelho) + ponteiro de volta à origem
no git; para os nativos (Produto, Marketing…), o texto (também markdown) mora
ali mesmo. Em ambos, **markdown é o formato canônico e quem renderiza é o
Brainiac** — o HTML é cache derivado, não a fonte.
_Avoid_: listagem, biblioteca, repositório, acervo

**Metadado**:
Os campos estruturados que classificam um Documento para navegação humana e
recuperação por IA.
_Avoid_: tag (tag é uma espécie específica de metadado), atributo

**Faceta**:
Um Metadado usado para filtrar e recuperar Documentos dentro de um Propósito
(ex.: departamento, publico_alvo). É o que substitui a "pasta por departamento".
_Avoid_: filtro, dimensão, categoria

**Coleção**:
Uma view curada e **ordenada** que agrupa vários Documentos já existentes para um
público ou objetivo (ex.: a trilha de onboarding). Não é um Propósito. Carrega uma
**narrativa própria** (corpo markdown, nativo) além da lista ordenada — traz contexto
_e_ aponta; os links do corpo resolvem para outros Documentos como em qualquer doc
nativo. O que a **define** é a lista ordenada de Documentos (a trilha, navegável e
reutilizável), não o corpo: sem essa lista é um Documento, não uma Coleção. Não tem
`proposito`/`formato`/facetas (é objeto de curadoria, não um átomo do catálogo) e
não aninha outra Coleção. Handbook é uma Coleção específica.
_Avoid_: trilha, pasta, handbook, acervo

**Departamento**:
Entidade de 1ª classe registrada no Brainiac que **agrupa membros** (usuários) de uma
área da empresa e é a **dona** da documentação sobre si mesma e da que seus membros
produzem sobre Projetos. É a tabela por trás das facetas `departamento` e
`publico_alvo` e, quando o Documento não tem Projeto, o prefixo do seu `id`. Não é
fronteira de acesso: todo Documento segue visível para a empresa inteira. Criar ou
arquivar um Departamento é ato governado (permissão `departments.manage`); um
Departamento se arquiva, nunca se apaga. Exemplos hoje: TI, Negócio, Produto,
Marketing, Design (todos separados — Negócio não é guarda-chuva; Design não vive
dentro de Produto).
Ver [Departamento é entidade de 1ª classe](docs/adr/0015-departamento-entidade-de-primeira-classe.md).
_Avoid_: área, setor, time, squad, tenant

**Membro** (de Departamento):
Um usuário vinculado a um Departamento. Vínculo puro, sem papel: uma pessoa pode ser
membro de vários Departamentos, e ser membro não trava nada — o dono de um Documento
não precisa ser membro do Departamento dele (convenção, não regra).
_Avoid_: funcionário, integrante, dono

**Projeto**:
Entidade de 1ª classe registrada no Brainiac, com `nome_negocio`, `nome_tecnico`,
`slug` e `sigla`. É o contêiner dos documentos e a "origem" da federação. Resolve
o desalinhamento de nomes (negócio × TI) sendo a camada de tradução.
Ver [Projeto é entidade de 1ª classe](docs/adr/0006-projeto-primeira-classe-sigla-canonica.md).
_Avoid_: sistema, repo, produto

**Sigla**:
O handle canônico de um Projeto (ex.: `RPQ`). Alinha negócio ↔ TI ↔ rastreador ↔
catálogo; é a "origem" dos ids qualificados (`RPQ:adr/0001`) e o prefixo do
rastreador (`RPQ-STORY-123`).
_Avoid_: código, acrônimo, prefixo

**projeto** (faceta):
A faceta que diz a qual Projeto o Documento pertence — referencia um Projeto pela
`sigla`. Multi-valor e opcional.
_Avoid_: sistema, módulo, repo, produto

**Vocabulário controlado**:
A regra de que as facetas (`proposito`, `departamento`, `publico_alvo`, `projeto`)
só aceitam valores de uma lista fechada — nunca texto livre. A lista é um enum no
código (`proposito`) ou uma tabela governada (`departamento`, `publico_alvo`,
`projeto`).
Apenas `palavras_chave` é livre. Existe para não quebrar filtro e recuperação
por IA.
_Avoid_: enum, lista, taxonomia fechada

**id** (campo):
O identificador canônico e estável do Documento. Não há id global cunhado pelo
catálogo: cada origem é dona do seu id nativo e o catálogo apenas **qualifica com
a sigla** do Projeto (ex.: `RPQ:adr/0001` (global), `RPQ:pagamentos/adr/0001` (de
módulo), `RPQ:PRD-12`). Quando o Documento não tem Projeto (`projeto: []`), o prefixo
cai para o **Departamento** dono (`departamento`) — ex.: `DESIGN:how-to/handoff-design-dev`;
por isso nenhuma sigla de Projeto pode colidir com o prefixo de um Departamento. Nunca muda, mesmo
que título ou facetas mudem; é o que relacionamentos e links referenciam.
Ver [Projeto é entidade de 1ª classe](docs/adr/0006-projeto-primeira-classe-sigla-canonica.md).
_Avoid_: código, número, DOC-NNNN, slug (slug é outra coisa)

**slug** (campo):
A parte legível e cosmética da URL de um Documento (ex.: `setup-ambiente`). Pode
mudar livremente sem quebrar o `id`.
_Avoid_: id, permalink

**resumo** (campo):
Uma a três frases que descrevem o Documento; serve ao mesmo tempo de preview para
humano e de sinal textual para a recuperação por IA.
_Avoid_: descrição, ementa, abstract

**palavras_chave** (campo):
O único campo de texto livre do Documento — uma lista de termos que serve tanto
para agrupar quanto para recuperar. Substitui a ideia de `tags` (não há campo
`tags` separado).
_Avoid_: tags, keywords, rótulos

**status** (campo):
O estado de ciclo de vida do Documento: `rascunho`, `revisão`, `publicado` ou
`obsoleto`. É um **sinal social**, não uma trava — a plataforma não impõe
aprovação (ver [Governança do PRD social por status](docs/adr/0008-governanca-do-prd-social-por-status.md)). Em `revisão` o
documento já é legível; em `publicado` passa a valer como a versão corrente/oficial
daquele Documento (o PRD, do produto; a spec, da implementação).
_Avoid_: estado, situação

**departamento** (faceta):
O Departamento que produz e mantém o Documento — o dono. Um só por Documento.
_Avoid_: time, dono, autor

**publico_alvo** (faceta):
Os Departamentos **para quem o Documento é relevante** — o público primário, usado para
navegação e recuperação. **Não** é controle de acesso: o Brainiac é interno e todo
Documento é visível para a empresa inteira; este campo só sinaliza relevância (e absorve
"as áreas que o documento trata"). Multi-valor. Dois sinais à parte, que não são
Departamentos: `toda_a_empresa` (relevante para a empresa toda; exclui a lista) e
`externo` (também voltado a público externo).
_Avoid_: audiência, destinatário, leitor, permissão, acesso, todos

## Propósitos

O Propósito é a espécie de conhecimento que um Documento entrega — o eixo de topo
da taxonomia. São três e são mutuamente exclusivos.
_Avoid_ (para "Propósito"): tipo, categoria, formato

**Referência**:
Fatos consultáveis pontualmente: API, esquema de um módulo, glossário, spec.
Você consulta, não lê de ponta a ponta.

**How-to**:
Passos para realizar uma tarefa ou seguir um rito/fluxo recorrente (inclusive um
handoff entre times, quando o que importa é executá-lo). Você lê para executar — e
também para **aprender fazendo**: por decisão de escopo, how-to absorve o modo
"tutorial" e o antigo propósito "processo" executável (não há propósito `tutorial`
nem `processo` à parte). Agrupar um fluxo de área vira Coleção; a _justificativa_ de
um handoff é explicação.
_Avoid_: tutorial, processo, guia, passo-a-passo, rito

**Explicação**:
Entendimento e contexto: regra de negócio, visão de arquitetura, o "porquê" —
inclui o registro de uma decisão e seu trade-off (um ADR) e a _justificativa_ de um
handoff. A "decisão" é capturada pelo formato ADR, não por um propósito à parte.
Você lê para entender.
_Avoid_: documentação técnica, overview, decisão (decisão é o formato ADR)

## Andares e fluxo de produto

**Brainiac**:
A plataforma central de documentação da empresa — o "portal central" que antes não
tinha nome. É o andar empresa: é onde **nasce o PRD** (fonte da verdade do produto),
**federa** (recebe por PUSH do módulo de doc) e espelha as docs de TI, **hospeda**
nativamente as docs não-técnicas e é a **porta única** para liderança e Produto.
Cada repo de TI continua dono da sua doc técnica; o Brainiac unifica o acesso.
Ver [Topologia de documentação híbrida](docs/adr/0002-topologia-hibrida.md), [Federação por PUSH pelo módulo](docs/adr/0009-federacao-por-push-modulo.md).
_Avoid_: portal central, portal, central, wiki, hub

**Federação**:
O módulo de doc de cada repo **empurra** (PUSH, via o comando `docs:publish`) um
snapshot da doc de TI para um **espelho de leitura** no Brainiac (metadado indexado

- markdown-fonte; quem renderiza é o Brainiac). O git continua a fonte da verdade do código; o Brainiac é a
  superfície de leitura em produção — os repos são privados e o `/docs` roda só em DEV,
  então não há de onde puxar ao vivo.
  Ver [Topologia de documentação híbrida](docs/adr/0002-topologia-hibrida.md), [Federação por PUSH pelo módulo](docs/adr/0009-federacao-por-push-modulo.md).
  _Avoid_: agregação, importação, índice remoto

**PRD**:
Documento de requisitos de produto que vive no Brainiac; dono é Produto;
versionado (última versão = fonte da verdade). Grão de uma feature ou grupo coeso
de features — nunca o projeto inteiro. Contém as regras de negócio como seção
interna. Major = muda comportamento (gera Spec); minor = ajuste de texto, declarado
pelo Produto no publish. Classifica-se como `formato: PRD` e `proposito: referencia`
(o TI o consulta para construir); a Visão de produto é o par `explicacao`.
Ver [Documentação de produto: PRD no Brainiac, Spec no repo](docs/adr/0003-doc-produto-prd-spec-repo.md), [PRD é a unidade central de produto](docs/adr/0007-prd-unidade-central-de-produto.md), [Ciclo de vida do PRD](docs/adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md).
_Avoid_: Regra, requisito

**regra de negócio**:
Uma afirmação normativa que o sistema deve obedecer (ex.: "voucher é de uso
único"). **Não** é um documento à parte — é uma seção dentro de um PRD.
_Avoid_: Regra (maiúsculo, como se fosse um documento à parte), política

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
Ver [Documentação é upstream do rastreador](docs/adr/0004-doc-upstream-do-rastreador.md).
_Avoid_: Monday, board, gestor de tarefas

## Formato e metadados

**Formato** (de documento):
A espécie concreta de documento, igual em todos os departamentos: README, CONTEXT,
reference, how-to, explanation, ADR, spec, plan, PRD. Cada Formato é **evergreen**
(edita-se o mesmo) ou **datado** (congela e cria-se um novo). É eixo distinto do
Propósito (que diz o conhecimento que o documento entrega).
_Avoid_: tipo, categoria

**Evergreen** (classe de Formato):
Formato cujo documento é **editado** para refletir o estado atual; existe um por
assunto (README, CONTEXT, reference, how-to, explanation; o PRD é evergreen
versionado).
_Avoid_: vivo, atual

**Datado** (classe de Formato):
Formato cujo documento é **congelado** num momento e nunca editado; cria-se um novo
a cada vez (ADR, spec, plan). Leem-se em conjunto.
_Avoid_: append-only, imutável, histórico

**Metadado core**:
Os Metadados que todo Documento carrega, em qualquer departamento ou formato (id,
titulo, resumo, proposito, formato, origem, departamento, publico_alvo, status,
owner, datas, palavras_chave, related). A base compartilhada.
_Avoid_: campos base, padrão

**origem** (campo):
De onde vem o texto de um Documento: `nativo` (escrito no Brainiac — PRD, Visão
de produto, doc de área não-técnica) ou `espelho` (empurrado por um repo de TI via
`docs:publish`; carrega ponteiro git + carimbo de sincronização). Vocabulário
controlado de dois valores.
_Avoid_: fonte, proveniência, tipo

**Extensão de departamento**:
Bloco de Metadados que só faz sentido para um departamento (ex.: `module` no TI,
`canal` no Marketing, `segmento` no Produto), sem poluir os Documentos das outras
áreas. Há também metadados **por formato** (ex.: `deciders` no ADR, `versao` no PRD).
_Avoid_: campo custom, metadado extra

**module** (extensão de TI):
A parte do sistema de que um Documento de TI fala — o seu **escopo**. Recebe o nome
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
Ver [Autoria do não-técnico por guideline](docs/adr/0005-autoria-nao-tecnico-guideline-paste.md).
_Avoid_: prompt, template, stub
