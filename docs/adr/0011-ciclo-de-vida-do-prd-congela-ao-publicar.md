# Ciclo de vida do PRD: o texto versiona e congela ao publicar

O PRD versiona **só o texto** (o contrato com o TI), e cada versão **congela ao
ser publicada**. **Salvar ≠ publicar:** salvar deixa um **rascunho legível** no
Brainiac (selo `rascunho`, visível mas ainda sem valer); **publicar** é um passo
deliberado em que o Produto **declara menor/maior** e a versão congela, virando a
verdade. **Metadado** (fora `status`) edita-se **no lugar**, sem nova versão.
Estende o [PRD é a unidade central de produto](0007-prd-unidade-central-de-produto.md) (menor/maior) e o
[Governança do PRD social por status](0008-governanca-do-prd-social-por-status.md) (status social).

## Por que

Depois de publicado, o TI **constrói em cima** do PRD (spec/plano/código) — o texto
vira contrato. Reescrevê-lo em silêncio é perigoso (o TI pode estar construindo a
versão antiga). Então a regra natural: publicou, **congelou**; mudou, é **versão
nova**. Antes de publicar (análise) o rascunho é editado à vontade — ninguém
depende dele ainda, mas ele já é legível.

## Decisões

- **Quem declara menor/maior é o Produto, no publish.** Detecção automática é
  inviável — é julgamento de **intenção** (uma palavra trocada pode ser cosmética
  ou virar o jogo); nem uma IA decide isso com confiança. Coerente com o `status`
  social: o sistema **reflete**, não policia. Como toda mudança de texto já vira
  versão, um rótulo errado só significa "o TI talvez não recebeu o sinal de spec" —
  e o TI, que lê o PRD, pode pedir o bump.
- **A versão menor é silenciosa.** Só a **maior** aciona o caminho de Spec (muda
  comportamento → nova Spec); a menor é um congelamento que **não exige ação do TI**
  — a Spec referencia a versão exata (`@v2.0`) e não se mexe sozinha. Assim, corrigir
  um typo deixa rastro na pilha **sem** disparar um falso sinal de "contrato mudou".
- **Só o texto congela.** Classificação (título, resumo, palavras-chave, público,
  relacionados) é sobre **achar** a Entrada, não sobre o contrato — edita no lugar,
  mesmo num PRD publicado. `status` é a exceção: é a **chave do ciclo de vida**.
- **A última publicada continua sendo a verdade pro TI** enquanto uma nova versão
  está em rascunho; o rascunho fica **disponível para leitura** (selo), mas não
  desbanca o que o TI já constrói até ser confirmado.

## Consequência no modelo de dados

O PRD **não é uma linha que se sobrescreve**: é uma **Entrada** que aponta para uma
**pilha de versões congeladas** (texto + número + status + datas por versão). A
última publicada é a verdade; as antigas viram histórico; a Spec do TI referencia a
**versão exata** (`RPQ:PRD-12@v2.0`). Como cada versão guarda o texto integral, o
Brainiac pode exibir o **diff entre versões** (no publish e na pilha) como recurso de
leitura — visibilidade do que mudou, não um gate.

## Opções consideradas

- **Editar texto publicado no lugar para correções pequenas (errata).** Rejeitada:
  exige alguém julgar "é pequeno o suficiente?" a cada vez; congelar sempre é mais
  simples e honesto (até o typo deixa rastro como versão menor).
- **Sistema decidir menor/maior por diff.** Rejeitada: inviável e indesejável como
  gate — é decisão de intenção, do humano.
