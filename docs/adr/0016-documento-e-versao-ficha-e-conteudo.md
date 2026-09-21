# Documento é a ficha, Versão é o conteúdo: o split ficha × conteúdo continua, unificado em 1:N

A **Entrada** deixa de existir como termo. A ficha do catálogo passa a se chamar
**Documento** (`catalog_documents`) e o corpo markdown passa a se chamar **Versão**
(`catalog_document_versions`), numa relação 1:N que absorve as duas tabelas de corpo de
hoje, `catalog_documents` (1:1, corpo único) e `catalog_prd_versions` (1:N, só PRD). O
espelho federado é o caso degenerado: exatamente uma Versão, sobrescrita a cada ingest.

## Por que o split continua

Com a premissa de que versionar deixa de ser exclusivo do PRD e vira capacidade de todo
conteúdo nativo, o corpo de um Documento nativo é inerentemente 1:N. Consideramos
colapsar o corpo corrente numa coluna da ficha (modelo _working copy_: `body_markdown`
na ficha, cópias congeladas à parte). Rejeitado: a verdade passaria a existir em dois
lugares que precisam bater, e o leitor que abre a ficha veria o rascunho por padrão,
invertendo a regra do [ADR 0011](0011-ciclo-de-vida-do-prd-congela-ao-publicar.md) de
que o rascunho não desbanca a versão publicada. Manter o corpo fora da ficha também
deixa a listagem do catálogo leve e guarda `mentions` junto do texto que o gerou, base
da invalidação de cache do [ADR 0010](0010-markdown-canonico-render-centralizado.md).

Manter as duas tabelas de corpo como estão foi rejeitado porque `documents` e
`prd_versions` já repetem as mesmas colunas e só divergem no que o PRD acrescenta
(numeração, estado, data de congelamento).

## Por que os nomes mudam

No dia a dia todo mundo chama o conjunto ficha + corpo de "documento"; "Entrada" exigia
tradução em toda conversa, e "Documento" como sinônimo de "corpo markdown" ficou sem
sentido quando o corpo virou um estado do texto. **Versão** já existia no glossário
para o PRD ("um estado congelado do texto"); generalizá-la é o caminho mais curto.
"Revisão" foi rejeitado porque já é um valor de `status`.

Convenção mantida: glossário em pt_BR (Documento, Versão), código em inglês
(`Document`, `DocumentVersion`; tabelas `catalog_documents`,
`catalog_document_versions`; pivots `document_project`, `collection_document`;
`document_links`).

## Onde mora o `git_pointer`

Sobe para o Documento, nullable, preenchido só quando `origin = mirror`. É proveniência,
como `native_id`, `project_id` e `origin`, não um fato do texto: quando o TI move o
arquivo no repo e republica, é a ficha que ganha um ponteiro novo, o corpo pode ser
idêntico. A resolução de menções (caminho relativo no repo → `git_pointer` → URL do
Documento) passa a bater direto na tabela que tem a URL. A Versão fica só com
`body_markdown` e o que deriva dele (`mentions`).

## Consequências

1. `catalog_prd_versions` some; o que o PRD acrescentava à Versão (numeração, estado,
   congelamento) é redesenhado na decisão sobre versionar todo conteúdo nativo.
2. `PrdVersion`, `Document` (no sentido antigo) e `Entry` são substituídos por
   `Document` e `DocumentVersion`. `Entry*` renomeia em bloco: `EntryLink`,
   `entry_project`, `collection_entry`, `EntryFactory`, Actions `CreateNativeEntry` e
   `UpdateNativeEntry`.
3. Invariante: **todo Documento tem pelo menos uma Versão** (ver
   [ADR 0017](0017-remove-artefato.md), que retira a outra forma de conteúdo).
4. Supersede a seção "Princípio central: ficha × conteúdo" da
   [spec de 2026-07-05](../specs/2026-07-05-modelagem-de-dados-do-catalogo.md). O
   [ADR 0014](0014-dois-modulos-catalog-e-apresentacao.md) segue válido lendo
   "Entrada como raiz de agregado" como "Documento como raiz de agregado".
