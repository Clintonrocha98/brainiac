# Pesquisa — confronto do Brainiac com o estado da arte

Esta pasta guarda **pesquisa de apoio ao desenho** do Brainiac: o confronto das nossas
decisões com a literatura e a prática de modelagem de documentação (open-source e
empresas), feito para achar pontos cegos e enriquecer o desenho.

O relatório original era um arquivo único, grande demais para avaliar. Foi **quebrado
por tema e por prioridade** — cada tema reescrito com linguagem mais simples, antes/depois
e a solução proposta, e cada ponto com um campo **decisão: pendente** para triagem.

> **Natureza deste material:** é **insumo de pesquisa, não decisão canônica.** Foi gerado
> por pesquisa adversarial (14 tradições, 2 rodadas) em 2026-06-27. A fonte da verdade do
> desenho continua sendo o [`docs/arquitetura.md`](../arquitetura.md), o glossário
> [`CONTEXT.md`](../../CONTEXT.md) e os [ADRs](../adr/). Uma recomendação daqui só passa a
> valer quando for **triada e aceita** — e, nesse caso, vira um ADR.

## A lente de prioridade

Os temas estão ordenados pelo foco desta fase:

| Prioridade | Significado |
|---|---|
| **▶ Foco** | Problemas que a modelagem pode trazer no futuro · melhorar os tipos de documento · polir o que existe |
| **⏸ Depois** | Vale, mas atrasaria o objetivo principal (gerar documentação); reavaliar com a base já em volume |
| **⏸ Adiado** | Fora de escopo agora (Brainiac é interno); conteúdo preservado, não apagado |

## Os temas

| Tema | Prioridade | Do que trata |
|---|---|---|
| [1 · Tipos de documento](temas/1-tipos-de-documento.md) | ▶ Foco | Os 5 propósitos se sobrepõem · `departamento` 1→N · ciclo do PRD · regra cross-feature |
| [2 · Frescor e disposição](temas/2-frescor-e-disposicao.md) | ▶ Foco | `obsoleto` não faz nada · classe de retenção · "revisado em" · deleção propaga |
| [3 · Evolução e migração](temas/3-evolucao-e-migracao.md) | ▶ Foco | Sem `schema_version` · migração em fases · id preso à sigla |
| [4 · Polir o que existe](temas/4-polir-o-que-existe.md) | ▶ Foco | ADRs sem cabeçalho · wikilinks quebrados · sem verificação automática (o que já caiu vem marcado) |
| [5 · Recuperação por IA](temas/5-recuperacao-por-ia.md) | ⏸ Depois | Busca é só pré-filtro · FTS · chunk · llms.txt |
| [6 · Segurança](temas/6-seguranca.md) | ⏸ Adiado | Modelo de ameaça · stored-XSS · webhook · iframe |
| [7 · Acessibilidade](temas/7-acessibilidade.md) | ⏸ Adiado | Campos de a11y no schema · linguagem simples |

[fontes.md](fontes.md) reúne todas as leituras (com URL) e um digest do que cada
tradição trouxe.

## O diagnóstico em uma frase

Todos os temas convergem num ponto só: **o Brainiac construiu os pontos de controle
certos, mas não escreveu a invariante em cima deles.** Tem estado `obsoleto` — mas
nenhuma ação acoplada a ele. Tem ingest determinístico — mas não deriva versão de
schema. Tem snapshot idempotente — mas nenhuma rede contra deleção acidental. A alavanca
existe; a regra, não.

E há um corolário que costura tudo: o projeto **não comia a própria comida** (o resíduo
das duas sessões já provou que disciplina humana pura falha sem verificação). Parte disso
**já foi corrigida** no polimento — o tema 4 separa o que caiu do que falta.

## Próximo passo sugerido

Triar tema a tema, marcando cada **decisão: pendente** como aceita / rejeitada / adiada.
Os pontos aceitos de severidade alta viram ADRs (a numeração seguiria de
ADR-0013 em diante). Esta pasta permanece como o registro da pesquisa que motivou
essas decisões.
