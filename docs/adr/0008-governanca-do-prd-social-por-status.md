# Governança do PRD é social e dirigida por status, não um gate de sistema

A plataforma **não impõe** aprovação rigorosa (sem RBAC nem workflow de aprovação
por enquanto). O campo `status` é um **sinal social**, não uma trava.

Fluxo:

1. Produto escreve o PRD → `rascunho` (WIP).
2. Pronto para revisão → `revisão`: já **legível** no portal, para TI e PO
   revisarem via reuniões/mensagens.
3. Alinhado → o **próprio Produto** troca para `publicado`, tornando o PRD a fonte
   da verdade e **liberando o TI** a criar a Spec/ADR (e o PO a criar as tasks no
   Monday — [ADR-0004](0004-doc-upstream-do-rastreador.md)).
4. Substituído → `obsoleto`.

Consideramos gate único (PO aprova como permissão de sistema) e gate duplo
(Produto + tech-lead de TI). Adiados: o time é pequeno e a confiança + a discussão
informal bastam hoje; a plataforma só **reflete** o estado, não o policia.

Consequência: a corretude depende da disciplina do time (qualquer um poderia
publicar). Aceitável agora e revisável quando o time crescer — dá para adicionar
permissões depois **sem mudar o modelo de estados** (só adicionando quem pode
fazer cada transição).
