---
supersedes: 0011
---

# Versionar é do conteúdo nativo: pilha universal, congelamento no publish, `major` booleano

Congelar deixa de ser privilégio do PRD. **Todo Documento nativo** acumula uma pilha
de Versões e congela ao publicar; o **espelho** federado continua sendo o caso
degenerado, com uma Versão só. Cada Versão congelada recebe um **número sequencial**
por Documento (`RPQ:PRD-12@v7`) e uma marca booleana **`major`**, declarada pelo humano
no publish. Supersede o
[ADR 0011](0011-ciclo-de-vida-do-prd-congela-ao-publicar.md), que desenhava esse ciclo
só para o PRD; estende o
[ADR 0016](0016-documento-e-versao-ficha-e-conteudo.md), que criou a tabela de Versões
e deixou em aberto o que o PRD lhe acrescentava.

## Por que

O raciocínio do ADR 0011 nunca foi sobre PRD: _depois de publicado, alguém constrói em
cima do texto; reescrevê-lo em silêncio é perigoso_. Isso vale para o how-to que o
Marketing segue, para o `reference` de API que o TI consulta e para a Visão de produto
que a liderança cita numa reunião. Prender a capacidade a um formato obrigava a escolher
entre "nunca tem rastro" e "vira PRD", e mantinha o **Formato** governando o
comportamento do conteúdo — uma regra por espécie de documento, justo o que este esforço
está removendo do modelo.

## Decisões

### Salvar sobrescreve o rascunho; publicar congela

Existe **no máximo uma Versão não congelada** por Documento, e salvar sobrescreve essa
linha. A pilha cresce no **publish**, não no teclado: o histórico guarda publicações, não
salvamentos. A regra "no máximo 1 rascunho por Entrada" do ADR 0011 deixa de ser regra
escrita e vira estrutura.

Nenhum discriminador entra na ficha. **Evergreen × Datado deixa de ser regime de dados**
e volta a ser o que sempre foi de fato — hábito editorial: o README é republicado no
lugar do anterior, o ADR nasce novo a cada decisão, e os dois usam a mesma pilha.

### Número sequencial, cunhado no congelamento

`@v1`, `@v2`, … por Documento, igual em todo formato. Quem cunha é o congelamento, então
**rascunho não tem número** e **espelho não tem número** — o espelho não publica,
sincroniza.

### `major` booleano no lugar de maior/menor

O par `major`/`minor` sai. Pelo próprio ADR 0011, o `minor` nunca carregou informação
além da ordem — quem trabalhava era o sinal `major`, que avisa "o contrato mudou, nasce
Spec nova". Ele vira um booleano declarado no publish (default `false`), e a ausência
**é** o menor. O que o ADR 0011 diz continua valendo palavra por palavra: quem declara é
o humano, porque é julgamento de intenção; a versão menor é silenciosa; só a maior aciona
o caminho de Spec. Muda a notação: `RPQ:PRD-12@v2.0` passa a ser `RPQ:PRD-12@v7`, com v7
marcada como maior.

### Um estado mecânico na Versão, um sinal social na ficha

`frozen_at` nulo **é** a definição de rascunho; o enum `PrdVersionState`
(`draft`/`frozen`) sai por redundância — hoje ele repete o que o timestamp já diz e
admite a linha incoerente `frozen` com `frozen_at` nulo. O `status`
(`rascunho`/`revisão`/`publicado`/`obsoleto`) fica onde está, na ficha, como o sinal
social do [ADR 0008](0008-governanca-do-prd-social-por-status.md), editável no lugar.
São eixos com papéis diferentes: a Versão diz um **fato** sobre o texto, a ficha diz uma
**intenção** sobre o Documento.

### Uma regra de leitura, sem ramo por origem

O leitor recebe a **última Versão congelada**; o rascunho aparece por selo, nunca no
lugar dela. Documento nativo que nunca publicou não tem verdade corrente — mostra o
rascunho com selo, como o ADR 0011 já previa.

### O espelho: congelado na sincronização

Uma Versão, `frozen_at` recebendo o carimbo do ingest, sem número e nunca `major`.
"Congelada" quer dizer no espelho exatamente o que quer dizer no nativo: **este texto não
se edita no Brainiac**. Quem escreve é o git, e o `ReconcileSnapshot` substitui a linha
inteira, não a edita. Com isso a regra de leitura vale para as duas origens sem `if`.

## Consequências

1. `catalog_document_versions` fica com `number` (nullable, sequencial por Documento),
   `major` (bool, default `false`), `frozen_at` (nullable), `body_markdown` e `mentions`.
   `state` não existe.
2. `PrdVersionState` e `FreezePrdVersion` são substituídos por um congelamento genérico
   que cunha o número e grava `major`.
3. `ReconcileSnapshot` passa a carimbar `frozen_at` a cada ingest.
4. O **Formato perde a função de decidir o comportamento do conteúdo** — insumo direto
   para a decisão sobre Propósito × Formato.
5. O diff entre Versões, que o ADR 0011 previa como recurso de leitura do PRD, passa a
   valer para qualquer Documento nativo.
6. Fica em aberto a quem um link aponta agora que a Versão é citável: à ficha ou a uma
   Versão específica.

## Opções consideradas

- **Pilha literal (cada salvamento vira Versão, estilo git).** Rejeitada: o histórico
  viraria log de teclas, misturando rascunho e marco, e a tabela cresceria sem que
  nenhuma das linhas fosse uma verdade que alguém publicou.
- **Dois regimes decididos pelo Formato** (evergreen sobrescreve, datado empilha).
  Rejeitada: mantém o Formato governando comportamento e cria dois tipos de Documento
  que o leitor precisa distinguir.
- **Dois regimes decididos por um campo na ficha** (`versiona: sim/não`). Rejeitada: o
  mesmo formato passaria a se comportar de dois jeitos conforme quem criou.
- **`major`/`minor` universal.** Rejeitada: impõe o ritual de julgar intenção em todo
  documento, inclusive nos que ninguém usa como contrato.
- **Data de congelamento como identificador da Versão**, sem número. Rejeitada:
  referência ruim de falar em voz alta e ambígua com dois publishes no mesmo dia.
- **Descer o `status` social para a Versão.** Rejeitada: contraria o ADR 0008 e deixa
  `obsoleto` sem casa — obsoleto é sobre o Documento, não sobre um texto.
- **Derivar o `status` da pilha** (tem congelada = publicado). Rejeitada: `revisão` e
  `obsoleto` deixam de ser representáveis.
- **Espelho empilhando a cada push.** Rejeitada: duplica no Postgres um histórico que o
  git já guarda melhor ([ADR 0002](0002-topologia-hibrida.md)), sem autor e sem commit.
- **Renomear `frozen_at` para `effective_at`.** Rejeitada: o caso principal é o nativo,
  onde "congela ao publicar" é o vocabulário certo; o degenerado herda o nome do
  principal, não o contrário.
