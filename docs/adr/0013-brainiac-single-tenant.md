# Brainiac é single-tenant; a máquina de tenancy do scaffold é removida

O Brainiac é um sistema **interno de uma única empresa**. Toda Entrada é visível
para a empresa inteira e `publico_alvo` sinaliza **relevância, não acesso** (ver
[Taxonomia de documentação orientada a propósito](0001-taxonomia-orientada-a-proposito.md)) — não há
isolamento de dados a fazer. Portanto o Brainiac é **single-tenant** e **nenhuma
tabela do catálogo carrega `tenant_id`**.

Removemos a máquina de multi-tenancy herdada do scaffold he4rt/sycorax (o `Team`
como tenant, o concern `InteractsWithTenants`, o escopo por tenant). O
`AdminPanelProvider` já não registrava `->tenant(...)` — o código nunca chegou a
ligar a tenancy; esta decisão a torna explícita e remove o resíduo.

A **Projeto** continua entidade de 1ª classe (ver [Projeto é entidade de 1ª classe](0006-projeto-primeira-classe-sigla-canonica.md)),
mas como **contêiner e origem da federação**, nunca como fronteira de isolamento:
uma Entrada pode ser projeto-less (`projeto: []`) e uma Coleção cruza projetos —
os dois seriam impossíveis sob um tenant.

Consideramos manter a máquina dormente (um único tenant). Rejeitado: custo
cognitivo e um `tenant_id` inútil em toda tabela, sem nenhum ganho de isolamento.

Consequência: (1) desviamos deliberadamente da guideline de multi-tenancy do
`CLAUDE.md` — ela é herança do scaffold e deve ser **removida/atualizada**;
(2) `Team` e `ExternalIdentity` do scaffold saem de escopo; **Permissions/RBAC
fica** (acesso ao painel e distinção autor × leitor), mas **não** vira gate do
ciclo de vida do PRD — publicar segue ato social
([Governança do PRD social por status](0008-governanca-do-prd-social-por-status.md)); (3) revisável se um dia surgir necessidade de espaço
privado por área — mas o modelo de dados **não** assume isso hoje.
