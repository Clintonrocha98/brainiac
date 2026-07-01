# Evolução e migração — como o schema e os identificadores mudam com o tempo

> **Foco.** Outro grande "problema do futuro": o desenho assume que a forma de uma
> Entrada é fixa. Mas o schema **vai mudar** (novos campos, cardinalidade diferente,
> renomear coisas), e hoje não há um jeito seguro de mudar sem quebrar o que já existe.
> Com ~20 arquivos o projeto **já** falhou em se manter consistente uma vez —
> multiplique isso por milhares de Entradas federadas.

## O que já está certo (não mexer)

- **Vocabulário controlado de baixa cardinalidade.** É exatamente o que **torna
  possível** comparar versões de schema e nomear mudanças. A higiene já existe — falta
  usá-la também no eixo "versão".
- **id estável desacoplado de slug**, e a recusa de cunhar um id universal. Ver
  [Projeto é entidade de 1ª classe](../../adr/0006-projeto-primeira-classe-sigla-canonica.md).
  A crítica abaixo é só sobre o id estar amarrado à **sigla**, que é mutável.

---

## 1. Não há versão de schema — o ingest é tudo-ou-nada · severidade alta

**O problema.** Não existe um campo `schema_version` no front-matter. O ingest é
**binário**: ou o documento bate com a forma de hoje, ou é inválido. Não há "este é
válido na forma v1, basta converter para v2". Quando um módulo estiver desatualizado,
ele é rejeitado em bloco — ou pior, sobrescreve o espelho com a forma antiga via
"deleção propaga".

**Antes (hoje)**

```
documento recebido ──► bate com o schema de hoje? ──► sim: aceita
                                                  └──► não: REJEITA (em bloco)
```

**Depois (proposto)** — o documento declara sua versão e o ingest **converte**:

```yaml
# no front-matter e no envelope do snapshot:
schema_version: 2
```
```
documento (v1) ──► upcast v1→v2→v3 (em memória) ──► valida na forma atual ──► aceita
                   (cadeia de conversões determinística)
```

**Solução proposta.** Adicionar `schema_version` ao front-matter e ao envelope do
snapshot; o ingest faz um *upcast* determinístico (aplica a cadeia de migrações em
memória) **antes** de validar. Assim um módulo que ainda emite a forma antiga continua
funcionando, e fica **visível** quem está atrasado.

**Decisão:** _pendente_

---

## 2. Não há processo de migração em fases · severidade alta

**O problema.** Toda mudança de forma é um evento improvisado, não uma migração
planejada. A própria reconciliação entre a sessão 1 e a sessão 2 foi um exemplo: a
forma nova foi **proposta** (expand), mas não houve um *runner* que convertesse tudo
e removesse a forma velha de modo rastreável. (Boa parte desse resíduo **já foi
limpa à mão** no polimento — mas à mão, sem processo repetível.)

**O padrão recomendado** é o *expand / migrate / contract* (mudança em três fases que
nunca quebra quem ainda usa a forma antiga):

```
[1] EXPAND    adiciona a forma nova ao lado da velha (ambas válidas)
      │
[2] MIGRATE   converte os documentos existentes para a forma nova (backfill)
      │       — passo separado, em lotes, retomável
      │
[3] CONTRACT  remove a forma velha — só depois de ninguém mais usá-la
```

**Solução proposta.** Um ADR de "evolução de schema" adotando expand/migrate/contract;
uma pasta `docs/migrations/` com migrações numeradas; tratar futuras reconciliações
como uma migração com *runner* idempotente e *dry-run*; e separar "mudar o schema"
(rápido) de "converter os dados" (trabalho de fundo, em lotes).

**Decisão:** _pendente_

---

## 3. O id canônico está amarrado à sigla, que é mutável · severidade alta

**O problema.** Prometemos um id "estável, que nunca muda". Mas o id é **qualificado
pela sigla** (`RPQ:adr/0001`), e a sigla é o metadado mais sujeito a renomear, fundir
ou reciclar. Se `RPQ` virar `RPX` (rebrand, fusão de times), **todos** os ids,
relacionamentos, links de spec (`RPQ:PRD-12@v2.0`) e a chave de junção com o Monday
quebram de uma vez. Ver
[Projeto é entidade de 1ª classe](../../adr/0006-projeto-primeira-classe-sigla-canonica.md).

Há uma confusão de conceitos: a decisão queria não ser **autoridade** dos ids
(correto), mas isso virou **identidade que não sobrevive a um rename** (problema).

**Antes (hoje)**

```
id = RPQ:adr/0001
        └── a sigla faz parte do id  →  renomear a sigla quebra TUDO
```

**Depois (proposto)** — separar a identidade interna do rótulo legível:

```
identidade interna (opaca, imutável):  01J8...  (ULID por Entrada)
handle nativo da origem:               adr/0001
rótulo legível (resolvível):           RPQ:adr/0001   ← continua existindo, mas é alias

relacionamentos e chave de junção apontam para a identidade opaca, não para a sigla
```

**Solução proposta.** Pragmática para já: adicionar `aliases[]` e
`siglas_anteriores[]` + `sigla_canonica_atual`, para que renomear a sigla seja
**aditivo** (a velha continua resolvendo) em vez de um *big-bang*. A versão completa
(id opaco por baixo) pode vir depois.

**Decisão:** _pendente_

---

## 4. Mudar a cardinalidade de um campo não tem mecânica segura · severidade média

**O problema.** A mudança de `departamento` de 1 para N valores (discutida no tema
[Tipos de documento](1-tipos-de-documento.md)) é uma mudança de **cardinalidade** — e
hoje não há um jeito seguro de fazer isso sem reescrever tudo num único movimento
arriscado.

**Depois (proposto)** — fazer como *Parallel Change* (o mesmo expand/migrate/contract):

```
EXPAND    adiciona departamentos[] ao lado de departamento (singular)
MIGRATE   backfill: departamentos[] = [departamento] em todas as Entradas
          migra os leitores para ler do novo campo
CONTRACT  remove o departamento singular
```

**Solução proposta.** Decidir primeiro se são realmente co-donos (ver tema 1) e, se
forem, executar a mudança em fases. Mesma receita do ponto 2.

**Decisão:** _pendente_

---

## 5. Ninguém sabe quem ainda usa a forma antiga · severidade média

**O problema.** O Brainiac não tem como saber **quem ainda** referencia
`RPQ:PRD-12@v2.0` nem **qual módulo** ainda emite a forma antiga de um campo. O pin
`@v2.0` é só uma string que ninguém valida. Sem isso, todo passo de "remover a forma
velha" (CONTRACT) é um chute — e o **índice da IA** é o consumidor que sempre se
esquece de avisar.

**Depois (proposto)** — instrumentar quem consome e validar referências:

```
docs:publish de um consumidor  ──► "eu dependo de RPQ:PRD-12@v2.0"
                                        │
                                        └─► alvo não existe mais? → FALHA o publish
                                            (consumer-driven contract)
o Brainiac registra quem ainda pina cada versão / emite cada forma
  → CONTRACT só quando a lista esvazia
```

**Solução proposta.** Validar referências no publish (falhar se o alvo sumiu) e
registrar quem ainda usa cada forma/versão, para que a remoção seja baseada em dado, não
em chute.

**Decisão:** _pendente_

---

## Resumo para triagem

| # | Ponto | Severidade |
|---|---|---|
| 1 | Sem `schema_version`; ingest é tudo-ou-nada | alta |
| 2 | Sem processo de migração em fases | alta |
| 3 | id amarrado à sigla mutável | alta |
| 4 | Mudança de cardinalidade sem mecânica segura | média |
| 5 | Sem instrumentação de quem usa a forma antiga | média |

Os pontos 1, 2, 4 e 5 são o **mesmo ADR** ("evolução de schema"); o ponto 3 é um ajuste
no id qualificado pela sigla. Fontes (Fowler, Ambler/Sadalage, expand/contract):
[fontes.md](../fontes.md).
