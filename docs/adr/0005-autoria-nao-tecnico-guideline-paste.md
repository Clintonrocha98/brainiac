# Autoria do não-técnico: guideline + colar (v1), chat embutido (v2)

> **Generalização:** o princípio — **a IA gera o front-matter via guideline; o
> ingest é determinístico** — vale para **todo autor**, não só o não-técnico.
> Ninguém preenche metadado à mão. No TI, a "guideline" é a skill
> **grill-me-with-docs** (gera doc + front-matter no repo, que o módulo empurra —
> [Federação por PUSH pelo módulo](0009-federacao-por-push-modulo.md)); fora do TI, é a guideline colada
> no Claude web descrita abaixo.

O não-técnico autora documentos sem git, markdown ou formulário campo-a-campo.

**v1 (agora):** uma **Guideline de autoria** (prompt versionado) que a pessoa
roda no Claude web. A guideline embute o vocabulário controlado e faz o Claude web
emitir o **corpo + um bloco de front-matter** já nos valores válidos. A pessoa cola
o resultado no Brainiac, que **parseia o front-matter de forma determinística**,
valida contra o vocabulário canônico e **pré-preenche** uma tela de confirmação
(dropdowns) para revisar/ajustar antes de salvar. **Nenhuma IA roda no Brainiac** —
toda a geração acontece no Claude web, sob revisão humana.

**v2 (futuro):** chat conversacional embutido no Brainiac, eliminando o copia-cola.
Reaproveita o mesmo backend de parse/validação/salvar do v1.

Consideramos: (a) chat embutido já no v1 — melhor UX, mas a complexidade
determinístico × não-determinístico de hospedar um agente conversacional não se
paga agora; adiado para v2. (b) o Brainiac rodar extração por IA sobre texto cru
colado — rejeitado no v1 por introduzir não-determinismo dentro do sistema; a
geração fica onde a equipe já confia e revisa (Claude web).

Consequências: (1) a Guideline de autoria precisa carregar/referenciar o
vocabulário controlado e ficar em sincronia com ele; (2) o Brainiac precisa de parse
de front-matter robusto + validação + fallback manual (dropdown) para campos
faltando/inválidos; (3) o caminho de geração (Claude web) e o de ingestão (Brainiac)
são desacoplados — dá para evoluir cada um sem mexer no outro.
