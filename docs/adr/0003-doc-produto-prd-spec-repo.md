# Documentação de produto: PRD versionado no Brainiac, Spec datada no repo

Adotamos a **Opção A**: separar a documentação de produto por natureza.

O **PRD** vive no Brainiac como documento **versionado** (`v1.0`, `v2.0`…), de
dono **Produto**, sendo a **última versão a fonte da verdade**. Mudança de
**comportamento** = versão **major** (gera Spec + código); ajuste de
**texto/esclarecimento** = versão **minor** (não gera Spec). O histórico de
versões é o registro de como o PRD evoluiu.

A **Spec de cada implementação** vive **co-localizada no repo** (datada, imutável,
escrita por TI com a skill grill-me-with-docs) e **referencia a versão do PRD**
pelo id. O **Brainiac** federa essas Specs (recebe por push do módulo —
[Federação por PUSH pelo módulo](0009-federacao-por-push-modulo.md)) e mostra o PRD e suas Specs
amarrados num lugar só.

Consideramos: (B) tudo central — Produto dono do PRD **e** da Spec; e (C) tudo no
código — TI traduz produto para markdown. Rejeitados: B perde o registro
versionado-no-git do que cada PR atendeu; C mantém o não-técnico refém do TI para
editar/rever (a dor atual).

O PRD (linguagem de negócio, audiência Produto/liderança) e a Spec (como construir,
audiência TI) **não são redundantes** — são o mesmo fato em dois idiomas e
audiências. Encaixa na realidade atual: TI já escreve spec via grill-me-with-docs;
Produto documenta hoje em Google Docs solto (que o Brainiac substitui, cumprindo o
objetivo de padronizar e centralizar).
