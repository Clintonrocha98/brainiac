# Brainiac se organiza em dois módulos: `catalog` (domínio + federação) e a apresentação

O repo é um monorepo `internachi/modular`, onde cada módulo pertence a uma de três
camadas — **domínio** (regra de negócio), **integração** (fala com o mundo externo) e
**apresentação** (UI) — e vale a regra inegociável: *domínio nunca importa de
apresentação*. Hoje existem `identity` (domínio) e `panel-admin` (apresentação).
Precisávamos decidir onde mora o catálogo do Brainiac.

A modelagem mostrou que o Brainiac tem **um único negócio**: o catálogo, com a
**Entrada como raiz de agregado única** — um PRD *é* uma Entrada, uma Coleção aponta
para Entradas (ver [Modelagem de dados do catálogo do Brainiac](../specs/2026-07-05-modelagem-de-dados-do-catalogo.md)).
Não há sub-domínios independentes a separar (não é o caso de `identity`/`moderation`/
`economy` do scaffold). Logo, **não cortamos por negócio** — cortamos por **camada**,
na menor granularidade útil: **dois módulos**.

1. **`catalog`** (domínio, namespace `He4rt\Catalog\` seguindo o padrão do repo) —
   concentra todo o agregado (`entries`, `documents`, `prd_versions`, `entry_links`,
   `collections`, `projects`), as Actions, DTOs, Enums e o render. **A federação vive
   aqui**, como sub-namespace `Federation/`: reconciliar um snapshot (upsert +
   apaga-ausentes, numa transação, respeitando as invariantes da Entrada) *é* lógica
   de domínio que escreve nas próprias tabelas do catálogo — ver
   [Federação por PUSH pelo módulo](0009-federacao-por-push-modulo.md).
2. **Apresentação** — a autoria (Filament) e a vitrine de leitura seguem no
   `panel-admin` já existente. O domínio não a conhece.

Regra de dependência: `panel-admin` → `catalog`; `catalog` não importa ninguém. O
`Federation/` chama as Actions do próprio `catalog`.

Consideramos **um módulo único** (`brainiac` com Filament junto): rejeitado por ferir
a regra domínio-não-importa-apresentação. Consideramos **a federação como módulo de
integração próprio** (`integration-*`): rejeitado por ora — o transporte do webhook é
pequeno (uma rota + verificação HMAC + um `SnapshotDTO` + uma Action de reconciliação)
e a reconciliação já é domínio; um terceiro módulo seria cerimônia sem ganho.

Consequência: (1) só o **transporte** do webhook (rota + verificação HMAC) mora no
domínio — concessão consciente, o preço de não abrir um módulo de integração agora;
(2) se a federação crescer (múltiplas fontes, ETL pesado), extrair `Federation/` para
um módulo `integration-*` é refactor localizado — o sub-namespace já isola a fronteira;
(3) autoria e vitrine coexistem no `panel-admin`; se a leitura virar um portal público
separado, é decisão de apresentação posterior e **não** afeta o domínio.
