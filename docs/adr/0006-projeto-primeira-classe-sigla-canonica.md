# Projeto é entidade de 1ª classe; a sigla é o handle canônico e a origem dos ids

A **Projeto** é registrada manualmente no Brainiac com **nome de negócio**, **nome
técnico**, **slug** e **sigla**. A **sigla** (ex.: `RPQ`) é o handle canônico que
**alinha as nomenclaturas divergentes** — negócio chama de "Plataforma de
Recrutamento", TI chama de "recruit-party-quest" — sem forçar ninguém a renomear:
o Projeto é a camada de tradução.

A sigla é a **"origem" dos ids qualificados** do catálogo (`RPQ:adr/0001`,
`RPQ:PRD-12`) e já é o prefixo usado no rastreador (`RPQ-STORY-123`). Cada origem
é dona do seu **id nativo**; o catálogo apenas **qualifica** com a sigla — não
cunha id universal (supera a ideia de um `DOC-NNNN` único).

Documentos são criados **sob um Projeto**. A faceta `projeto` de uma Entrada passa
a referenciar uma Projeto pela sigla (não é mais texto/área genérica).

Consideramos: (a) id universal cunhado pelo catálogo — rejeitado porque o catálogo
viraria autoridade de docs federadas que não possui (mesmo princípio do
[Documentação é upstream do rastreador](0004-doc-upstream-do-rastreador.md)); (b) forçar um nome único por
projeto — rejeitado porque exigiria renomear repos ou mudar o vocabulário do
negócio.

Consequência: a sigla é a cola única entre **negócio ↔ TI ↔ rastreador ↔
catálogo**. O Projeto vira o contêiner natural dos documentos e o ponto de junção
da federação (a "origem").
