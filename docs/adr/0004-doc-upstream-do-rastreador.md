# Documentação é upstream do rastreador; o id do PRD é a chave de junção

A documentação é a **fonte da verdade**; o rastreador de tarefas (hoje Monday) é
uma **projeção/sub-produto** dela — não o centro. A ordem é sempre **doc → task**:
o PO cria as tasks a partir da doc do Brainiac (hoje manualmente via Claude + Monday
MCP; possível orquestrar pelo Brainiac depois).

O **id do PRD** (`PRD-NN`, cunhado pelo Brainiac) é a **chave de junção
canônica**: a Spec no repo referencia a versão do PRD; as tasks no Monday carregam
o id do PRD como referência de volta. O id interno do Monday (`RPQ-EPICO-…`,
`RPQ-STORY-…`) é apenas **organização local**, não identidade canônica.

Consideramos tornar o Brainiac a autoridade única de id (Monday recebendo o id do
Brainiac) e tratar o Monday como centro da verdade. Rejeitados: o primeiro cria duas
autoridades concorrentes para o mesmo item (drift); o segundo inverte a relação
que queremos (doc como verdade).

Consequência: a ferramenta de execução fica **intercambiável** — trocar o Monday
por outra não quebra nada, desde que a task carregue o id do PRD. Cada sistema é
dono apenas do seu próprio id (Brainiac → PRD; repo → Spec; Monday → rótulo local).
