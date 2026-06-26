# Documentação de produto: Regra versionada no central, Spec datada no repo

> **Refinado pelo [ADR-0007](0007-prd-unidade-central-de-produto.md):** o tipo
> "Regra" foi renomeado para **PRD** (grão de feature/grupo), e as regras de
> negócio viraram uma **seção dentro do PRD**. Leia este ADR trocando
> "Regra" por "PRD".

Adotamos a **Opção A**: separar a documentação de produto por natureza.

A **Regra de negócio** vive no portal central como documento **versionado**
(`v1.0`, `v2.0`…), de dono **Produto**, sendo a **última versão a fonte da
verdade**. Mudança de **comportamento** = versão **major** (gera Spec + código);
ajuste de **texto/esclarecimento** = versão **minor** (não gera Spec). O histórico
de versões é o registro de como a regra evoluiu.

A **Spec/PRD de cada implementação** vive **co-localizada no repo** (datada,
imutável, escrita por TI com a skill grill-me-with-docs) e **referencia a versão
da Regra** pelo id. O portal **federa** essas Specs (PULL) e mostra a Regra e suas
Specs amarradas num lugar só.

Consideramos: (B) tudo central — Produto dono de regra E spec; e (C) tudo no
código — TI traduz produto para markdown. Rejeitados: B perde o registro
versionado-no-git do que cada PR atendeu; C mantém o não-técnico refém do TI para
editar/rever (a dor atual).

A Regra (linguagem de negócio, audiência Produto/liderança) e a Spec (como
construir, audiência TI) **não são redundantes** — são o mesmo fato em dois
idiomas e audiências. Encaixa na realidade atual: TI já escreve spec/PRD via
grill-me-with-docs; Produto documenta hoje em Google Docs solto (que o central
substitui, cumprindo o objetivo de padronizar e centralizar).
