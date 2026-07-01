# PRD é a unidade central de produto (grão de feature/grupo)

Refina o [Documentação de produto: PRD no Brainiac, Spec no repo](0003-doc-produto-prd-spec-repo.md).

O documento central de produto chama-se **PRD** (não mais "Regra"). É de dono
**Produto**, vive no Brainiac, é **versionado** (última versão = fonte da
verdade) e tem grão de **uma feature ou grupo coeso de features** — nunca o
projeto inteiro (isso recriaria o Google Doc gigante atual).

As **regras de negócio são uma seção dentro do PRD**, não um tipo separado;
versionam junto com ele. Acima dos PRDs existe uma **Visão de produto**
(explanation, macro, evergreen, uma por Projeto: objetivo, escopo, personas,
roadmap).

Um documento de produto misto (como o doc real do "Firece Benefícios") é
**decomposto**: Visão Geral / Objetivo / Roadmap → Visão de produto; cada conjunto
de funcionalidades → um PRD; decisões técnicas (stack) → **ADR no repo do TI**
(não são do Produto). É o mesmo "dividir o misto" da sessão 1.

Cada versão major de um PRD gera uma **Spec** no repo. O id do PRD, qualificado
pela sigla do Projeto (ex.: `RPQ:PRD-12`), é a chave de junção — mantém o
[Documentação é upstream do rastreador](0004-doc-upstream-do-rastreador.md), só troca o nome Regra → PRD.

Consideramos: PRD por projeto inteiro (rejeitado — vira o documentão atual) e
regras como entidades atômicas separadas (adiado — mais reutilizável, mas mais
overhead; extrai-se sob demanda se uma regra provar ser cross-feature). Quando essa
extração ocorrer, o caminho previsto é a **transclusão por referência** — a regra vira
uma Entrada (id `RPQ:RN-07`, no mesmo esquema do `RPQ:PRD-12`) e o PRD a inclui por
chave (`{{ ref: RPQ:RN-07 }}`), fixando a versão ao congelar. **Nada disso se constrói
agora**: a nota existe só para o render ([Markdown canônico, render centralizado](0010-markdown-canonico-render-centralizado.md))
e o esquema de ids não fecharem essa porta.
