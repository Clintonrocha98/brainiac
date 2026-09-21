---
supersedes: 0012
---

# Artefato sai do modelo: sem Entrada só-artefato, sem embed em iframe

O conceito de **Artefato** (página HTML auto-contida referenciada por link e exibida
em iframe isolado, com a Entrada só-artefato em `catalog_entry_artifacts` e a coluna
derivada `has_artifact`) é retirado do catálogo. Supersede o
[ADR 0012](0012-artefato-asset-html-por-link-iframe-isolado.md) por inteiro.

## Por que

Ninguém vai usar. O Artefato nasceu para acomodar levantamentos visuais hospedados
fora do Brainiac, mas o catálogo se firmou como markdown canônico renderizado pelo
próprio Brainiac ([ADR 0010](0010-markdown-canonico-render-centralizado.md)). Manter
o conceito custava uma segunda forma de conteúdo, uma tabela, uma coluna derivada,
detecção por host no render e uma invariante de duas tabelas ("≥1 entre corpo e
artefato") que só a aplicação consegue garantir. Retirá-lo deixa uma forma de conteúdo
só, a Versão, e a invariante **todo Documento tem pelo menos uma Versão**.

## Consequências

1. `catalog_entry_artifacts`, `EntryArtifact`, `EntryArtifactFactory` e a coluna
   `has_artifact` somem.
2. Um link para HTML externo dentro do corpo markdown é um link comum: sem detecção
   por host, sem iframe `sandbox`, sem card expansível.
3. O verbete "Artefato" e a menção a "só-artefato" saem do glossário; o verbete
   "Documento" deixa de dizer que "não origina um Artefato".
4. Se um dia voltar a necessidade de embutir front-end arbitrário, o
   [ADR 0012](0012-artefato-asset-html-por-link-iframe-isolado.md) registra o desenho
   seguro (iframe de origem isolada) e as razões dele.
