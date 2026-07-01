# Polir o que já existe — o Brainiac comendo a própria comida

> **Foco.** A pesquisa apontou que o repositório **não segue as próprias regras** que
> prega. Parte disso **já foi corrigida** no polimento recente; este arquivo separa o
> que **já está resolvido** do que **ainda falta**, para você não perder tempo com o
> que já caiu.

## Já resolvido no polimento anterior ✓

A pesquisa foi gerada antes desta limpeza. Estes itens dela **não valem mais**:

- ✓ **Resíduo das duas sessões.** `exemplos-entradas.md` e `taxonomia.md` usavam o
  vocabulário morto (`DOC-NNNN`, `descricao`). Hoje usam ids qualificados pela sigla
  (`RPQ:pagamentos/reference/...`), `resumo` e o vocabulário corrente.
- ✓ **Arquivo de ADR que mentia.** O `0002-topologia-hibrida-pull.md` foi renomeado
  e reescrito como [Topologia híbrida](../../adr/0002-topologia-hibrida.md) — não fala
  mais em PULL.
- ✓ **Errata por blockquote no corpo dos ADRs.** As notas de transição foram removidas;
  os ADRs foram editados para o estado final limpo (decisão da fase de definição).
- ✓ **Conceito "tipo" ambíguo.** Resolvido com a separação **Propósito × Formato**
  (ver tema [Tipos de documento](1-tipos-de-documento.md)).

---

## Ainda falta

### 1. Os ADRs não têm um cabeçalho padrão · severidade média

**O problema.** Os doze ADRs não têm um bloco de metadados consistente (data, status)
nem o mesmo esqueleto de seções — os primeiros são prosa corrida, os últimos têm
cabeçalhos. Isso atrapalha a leitura comparativa e a extração por seção.

**Antes (hoje)** — formatos misturados:

```
0001–0008:  prosa corrida, sem cabeçalhos, sem data/status
0009–0012:  têm "## Por que", "## Consequências"… mas ainda sem data/status
```

**Depois (proposto)** — um esqueleto único + metadados mínimos:

```
---
status: accepted
date: 2026-06-27
---
# <título>
## Contexto   ## Decisão   ## Opções consideradas   ## Consequências
```

**Atenção à sua filosofia de fase de definição.** Você decidiu **não** deixar
marcadores de history (campos "superseded", notas de transição) enquanto não há produto
rodando. Um `status: accepted` + `date` num ADR vigente **não** é um marcador de
history — é só metadado de higiene, e ainda torna o próprio ADR um bom exemplo do schema
que pregamos. Marcar supersessão entre ADRs, esse sim, fica para quando houver história.
A decisão é sua: adotar o cabeçalho mínimo agora ou esperar.

**Decisão:** _pendente_

---

### 2. Os wikilinks `[[arquivo]]` não viram link no GitHub · severidade média

**O problema.** O glossário usa links no estilo Obsidian (`[[0012-artefato...]]`). No
GitHub eles **não renderizam** como link — viram texto entre colchetes, quebrando a
navegação (e a leitura por quem usa leitor de tela). Há ~10 deles em
[CONTEXT.md](../../../CONTEXT.md).

**Antes / Depois**

```
ANTES:   Ver [[0006-projeto-primeira-classe-sigla-canonica]]
DEPOIS:  Ver [Projeto é entidade de 1ª classe](docs/adr/0006-projeto-primeira-classe-sigla-canonica.md)
```

**Solução proposta.** Trocar os `[[wikilink]]` por links markdown relativos. É um
polimento puro, sem trade-off — alinhado com a fase de definição.

**Decisão:** _pendente_

---

### 3. `module` e `owner` são texto solto, não entidades · severidade média

**O problema.** `module` é uma string e `owner` é texto/e-mail. Não há entidade de
Time/Pessoa/Módulo, então não dá para perguntar "o que quebra se *pagamentos* mudar?"
nem "quais Entradas o time X possui". As relações são mantidas à mão.

**Antes / Depois**

```
ANTES:   module: pagamentos          owner: ana@empresa.com
DEPOIS:  module: → entidade Módulo    owner: → referência a Time/Pessoa (validada)
         (com dependsOn)             (distinta de departamento)
```

**Solução proposta.** Promover `module` a entidade sob o Projeto (com `dependsOn`) e
`owner` a referência validada. Isto é mais **direção futura** do que polimento imediato
— combina com o `owner` como referência do tema
[Frescor e disposição](2-frescor-e-disposicao.md). Provavelmente entra junto com o
modelo de dados real, não agora.

**Decisão:** _pendente_

---

### 4. Toda a aposta é em disciplina humana, sem nenhuma verificação automática · severidade alta

**O problema.** A consistência do vocabulário e o ingest determinístico são pedidos a
**humanos**, sem nenhuma ferramenta que verifique. Não há onde rodar: checagem de
links quebrados, validação do front-matter contra o vocabulário, detecção de markdown
que não renderiza, ou um *lint* que recuse o vocabulário aposentado (`DOC-NNNN`,
`descricao`) se ele reaparecer.

> Este é o fio que **conecta todos os temas**: a própria pesquisa diz que "disciplina
> humana pura já falhou aqui uma vez" — e foi justamente o resíduo das duas sessões,
> que tivemos de limpar à mão. Sem verificação, falha de novo em escala.

**Depois (proposto)** — uma verificação de qualidade no caminho de publicação:

```
publicar ──► [ checagem ] ──► aceita
              ├─ links quebrados?
              ├─ front-matter bate com o vocabulário + schema_version?
              ├─ markdown renderiza?
              └─ vocabulário aposentado reapareceu?  → falha
```

**Solução proposta.** Quando houver código, uma verificação por repositório como
**porteiro de qualidade** (separada do gatilho de publicação). Hoje, na fase de
definição, isso é uma **direção a registrar**, não algo a construir — mas é o que evita
o resíduo de voltar.

**Atenção.** Isto depende de código rodando (CI / hook). Não é polimento de documento;
é uma decisão de arquitetura para a fase de implementação. Listado aqui porque é a
**causa-raiz** de "não comer a própria comida".

**Decisão:** _pendente_

---

## Resumo para triagem

| # | Ponto | Severidade | Dá para fazer agora? |
|---|---|---|---|
| ✓ | Resíduo das sessões / topologia híbrida / erratas / "tipo" | — | **já feito** |
| 1 | Cabeçalho padrão nos ADRs | média | sim (parcial — ver filosofia) |
| 2 | Trocar `[[wikilinks]]` por links reais | média | **sim, puro polimento** |
| 3 | `module`/`owner` como entidades | média | não (direção futura) |
| 4 | Verificação automática (lint/CI) | alta | não (precisa de código) |

Os itens 1 e 2 são polimento imediato; 3 e 4 são direção para a fase de código. Fontes
(tradição ADR, GitLab/SSOT): [fontes.md](../fontes.md).
