# Documentação é upstream do rastreador; o id da Regra é a chave de junção

A documentação é a **fonte da verdade**; o rastreador de tarefas (hoje Monday) é
uma **projeção/sub-produto** dela — não o centro. A ordem é sempre **doc → task**:
o PO cria as tasks a partir da doc do portal (hoje manualmente via Claude + Monday
MCP; possível orquestrar pelo portal depois).

O **id da Regra** (`PROD-NN`, cunhado pelo portal central) é a **chave de junção
canônica**: a Spec no repo referencia a versão da Regra; as tasks no Monday
carregam o id da Regra como referência de volta. O id interno do Monday
(`RPQ-EPICO-…`, `RPQ-STORY-…`) é apenas **organização local**, não identidade
canônica.

Consideramos tornar o portal a autoridade única de id (Monday recebendo o id do
portal) e tratar o Monday como centro da verdade. Rejeitados: o primeiro cria duas
autoridades concorrentes para o mesmo item (drift); o segundo inverte a relação
que queremos (doc como verdade).

Consequência: a ferramenta de execução fica **intercambiável** — trocar o Monday
por outra não quebra nada, desde que a task carregue o id da Regra. Cada sistema é
dono apenas do seu próprio id (portal → Regra; repo → Spec; Monday → rótulo local).
