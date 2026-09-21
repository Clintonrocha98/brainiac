# Departamento é entidade de 1ª classe com membros; o enum `Area` sai do modelo

A **Área** deixa de ser uma lista fechada no código (enums `Area` e `Audience` do
módulo `catalog`) e vira **Departamento**: tabela `catalog_departments`, no módulo
`catalog`, ao lado de `catalog_projects`. Um Departamento existe para **agrupar
membros** da empresa e ser o **dono** da documentação do próprio departamento e da
documentação que seus membros produzem sobre Projetos.

## Onde mora

No `catalog`, não no `identity`. Departamento e Projeto são gêmeos estruturais: os
dois são vocabulário controlado promovido a tabela e os dois **prefixam o
`qualified_id`** (`RPQ:adr/0001` vem do Projeto; `DESIGN:how-to/handoff` vem do
Departamento). A regra "nenhuma sigla de Projeto colide com um nome de Departamento"
passa a valer entre duas tabelas **do mesmo módulo**, resolvida numa Action só. O
`identity` fica enxuto — `users`, `roles`, `permissions` — como o
[ADR 0013](0013-brainiac-single-tenant.md) já mandava.

Consideramos reaproveitar o `Team` do scaffold. Rejeitado: carrega `owner_id` e
`contact_email` obrigatórios, `TeamStatus` com `Suspended`, `softDeletes` e uma
policy escrita para tenancy — bagagem de um conceito que o ADR 0013 já removeu.
Sem restrição de compatibilidade, desenhar a tabela certa custa menos.

## O que é ser membro

Pivot **puro** `catalog_department_user` (`department_id`, `user_id`, timestamps),
N:N — uma pessoa pode estar em vários Departamentos. Sem coluna de papel: papel de
acesso é do RBAC spatie, não do pivot. Ser membro **não é trava**: `entries.owner_id`
segue livre, e a coincidência "dono é membro do departamento da Entrada" é convenção,
coerente com o [ADR 0008](0008-governanca-do-prd-social-por-status.md). Se surgir
"gestor do departamento", entra como permissão spatie, não como coluna.

## O que vira `publico_alvo` (`audience`)

O jsonb `audience` se decompõe em duas partes, porque `all` e `external` não são
Departamentos — não têm membros nem prefixam id:

- pivot `catalog_entry_audience` (`entry_id`, `department_id`) — os Departamentos
  para quem a Entrada é relevante;
- duas colunas booleanas em `catalog_entries`: `for_whole_company` e `for_external`
  (default `false`).

Mesma estrutura na Coleção (`catalog_collection_audience` + as duas flags).
Invariante garantida pela Action: `for_whole_company = true` ⇒ pivot vazio. O filtro
"relevante para X" é `pivot ∋ X OR for_whole_company`.

Rejeitamos linhas reservadas "Todos"/"Externo" na tabela (seriam departamentos
cadastráveis, com membros e prefixo de id) e "pivot vazio = todos" (colide com o
default `[department]` do contrato de federação e transforma esquecimento em
"para a empresa toda").

## Governança do vocabulário

Criar, editar e arquivar Departamento exige a permissão spatie `departments.manage`,
que por padrão só o papel admin possui. Explícito, sem conceito novo — reaproveita o
RBAC que o ADR 0013 manteve. Departamento **não se apaga**: recebe `archived_at`,
porque é prefixo de id e precisa continuar resolvendo.

## Consequências

1. Os enums `Area` e `Audience` do `catalog` são removidos. `catalog_entries.department`
   (string) vira `department_id` (FK); `audience` (jsonb) vira pivot + flags.
2. O resíduo do scaffold sai na implementação: `identity/src/Teams/`,
   `identity/src/ExternalIdentity/`, `app/Models/Concerns/BelongsToTeam.php`, e no
   `panel-admin` o `TeamResource` e as páginas de identidades externas. O login segue
   e-mail + senha via `LoginPage` do Filament, como já está.
3. O contrato de federação continua aceitando `department`/`audience` como strings;
   o ingest passa a **resolver referência** (string → linha) e a traduzir
   `all`/`external` para as flags. Detalhado em decisão própria.
4. A regra de cunhagem do id quando não há Projeto (prefixo cai para o Departamento)
   e a coluna que guarda esse prefixo ficam para a decisão sobre origem × assunto do
   Projeto.
5. Complementa o [ADR 0001](0001-taxonomia-orientada-a-proposito.md) (departamento e
   audiência continuam facetas, agora referenciando uma tabela) e o
   [ADR 0013](0013-brainiac-single-tenant.md) (Departamento não é tenant: toda Entrada
   segue visível para a empresa inteira).
