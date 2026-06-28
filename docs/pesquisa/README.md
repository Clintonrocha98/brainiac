# Pesquisa — confronto do Brainiac com o estado da arte

Esta pasta guarda **pesquisa de apoio ao desenho** do Brainiac: material que confronta
nossas decisões com a literatura e a prática de modelagem de documentação (open-source
e empresas). Serve para achar pontos cegos e enriquecer o desenho.

> **Natureza deste material:** é **insumo de pesquisa, não decisão canônica.** A fonte da
> verdade do desenho continua sendo o [`docs/arquitetura.md`](../arquitetura.md), o
> glossário [`CONTEXT.md`](../../CONTEXT.md) e os [ADRs](../adr/). Uma recomendação daqui
> só passa a valer quando for **triada e aceita** — e, nesse caso, vira um ADR.

## Conteúdo

- **[modelagem-documentacao.md](modelagem-documentacao.md)** — relatório adversarial
  completo. Confronta o desenho atual com **14 tradições** (Diátaxis, Docs-as-Code, DITA,
  Arquitetura da Informação, Grafos de conhecimento, prática de ADR, Backstage, GitLab
  Handbook, OSS em escala, RFC/PEP, RAG/AI-native, doc rot/freshness, Records Management
  e Migração de schema), em duas rodadas.

## Como navegar o relatório

| Seção | O que traz |
|---|---|
| Sumário executivo | Os achados dominantes em poucos parágrafos |
| Blind spots priorizados | 20 pontos cegos com severidade, fontes e recomendação concreta |
| Enriquecimentos propostos | Tabela de propostas com esforço estimado |
| Ataques por decisão/ADR | O que cada ADR (0001–0012) sofreu de crítica |
| Crítica estrutural dos nossos próprios documentos | Problemas de **forma** no próprio repo |
| Onde o Brainiac já acerta | O que **defender** — para não over-corrigir |
| Temas transversais · Fontes · Apêndice | Os fios condutores, as leituras (com URL) e o digest por tradição |

## Os fios condutores (resumo)

O relatório converge num diagnóstico só: **o Brainiac construiu os pontos de controle
certos, mas não escreveu a invariante em cima deles.** Os temas dominantes:

1. **Disposição** — `obsoleto` é estado terminal sem ação de saída; a pilha de versões só
   cresce e vira ruído ativo competindo com o sinal (problema distinto de *staleness*).
2. **Segurança** — tratada como checkbox, não como modelagem de ameaça; a costura
   `repo → webhook → espelho` nunca é desenhada como fronteira de confiança.
3. **Acessibilidade** — propriedade estrutural ausente do schema/render (a Entrada
   só-artefato pode ser 100% inacessível).
4. **Evolução de schema** — sem `schema_version` nem processo de migração; a própria
   reconciliação sessão 1 → sessão 2 travou no meio.
5. **Recuperação por IA** — no v1 é só pré-filtro por faceta (sem FTS no corpo, chunk,
   ranking ou `llms.txt`).
6. **Não comer a própria comida** — o resíduo das duas sessões prova, em três lentes, que
   disciplina humana pura já falhou aqui sem lint/CI.

## Próximo passo sugerido

Triar os blind spots de severidade **alta** → decidir o que entra → transformar os aceitos
em ADRs (a numeração sugerida no relatório começa em ADR-0013). Esta pasta permanece como
o registro da pesquisa que motivou essas decisões.
