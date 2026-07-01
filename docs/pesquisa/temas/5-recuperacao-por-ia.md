# Recuperação por IA — busca de verdade sobre o conteúdo

> **⏸ Adiado (mais à frente).** Você marcou este tema como posterior: investir nele
> agora **atrasaria o objetivo principal**, que é gerar documentação. O conteúdo fica
> aqui preservado para quando a base já tiver volume e a recuperação fizer diferença.
> Nada aqui precisa ser decidido agora.

## O ponto central · severidade alta (quando o tema entrar)

**O problema.** A promessa "recuperável por IA" está, no v1, **vazia**: é só um
**pré-filtro por faceta**. Filtrar por quatro facetas de baixa cardinalidade
(`proposito`, `departamento`, `publico_alvo`, `projeto`) restringe o escopo, mas
**nunca acha o trecho** que responde a uma pergunta. Não há "pedaço" (chunk) — só a
Entrada inteira, que estoura a janela de contexto. A busca textual está adiada e os
embeddings foram descartados. Ver o ponto aberto em [arquitetura.md](../../arquitetura.md).

**Antes (hoje)**

```
pergunta ──► filtra por faceta ──► lista de Entradas inteiras
             (pré-filtro de escopo; não encontra o parágrafo certo)
```

**Depois (proposto, em etapas)**

```
pergunta ──► filtra por faceta (pré-filtro)
         └─► busca no CORPO (Postgres FTS: tsvector + ts_rank)
         └─► retorna o PEDAÇO relevante (chunk), não a Entrada toda

roadmap declarado:  FTS lexical (já)  →  + pgvector (depois)  →  + reranking
destino: busca HÍBRIDA (a base é cheia de handles exatos — siglas, ids, códigos de
         erro — onde embedding puro falha; lexical + semântico juntos)
```

## O que mais a pesquisa trouxe (para o futuro)

- **Ligar o Postgres FTS já no v1**, na mesma rota para humano e para IA, sobre
  `titulo + resumo + corpo + palavras_chave`. É barato e já melhora muito.
- **`/llms.txt`** — um índice determinístico da base, pensado para consumo por IA.
- **Chunking pai-filho** — quebrar por H2/H3 (~200–400 tokens), devolvendo o pedaço
  com um cabeçalho de contexto pré-anexado (vindo do metadado), para o pedaço não
  chegar "solto" ao modelo.
- **Coleção que remonta conteúdo** — hoje a Coleção é só um índice de links; poderia
  renderizar como **um documento contínuo**.

## Acoplamento importante (por isso o cuidado de adiar junto)

A pesquisa alerta: **ligar a busca textual sem antes ter a regra de disposição** (tema
[Frescor e disposição](2-frescor-e-disposicao.md)) só faz o **ruído acumulado competir
melhor** com o conteúdo bom. Em outras palavras — quando este tema voltar, ele deve vir
**junto** com a disposição, não antes dela. Recuperação e disposição se desenham juntas.

**Decisão:** _adiado — reavaliar quando a base tiver volume_

Fontes (RAG/AI-native, llms.txt, chunking, Contextual Retrieval): [fontes.md](../fontes.md).
