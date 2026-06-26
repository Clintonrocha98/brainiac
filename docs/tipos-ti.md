# Tipos de documentação — contexto TI (por repo)

Co-localizado no repositório (ver [ADR-0002](adr/0002-topologia-hibrida-pull.md)).
Alinhado à guideline he4rt + uma camada evergreen para os docs duráveis que o
he4rt sozinho deixa sem lar.

## As duas classes

- **Evergreen** — você **edita** o mesmo doc para refletir o estado atual. Existe
  **um** por assunto; o histórico fica no git (implícito).
- **Datado (append-only)** — cada doc é **congelado** num momento; **nunca** é
  editado. Quando algo muda, cria-se um **novo**. Leem-se em conjunto.

## Tipos

| Tipo | Classe | Onde vive | Edita? | Lê para… |
|---|---|---|---|---|
| `README.md` | evergreen | raiz do módulo | sim | entrar no módulo (entrada prática + roadmap) |
| `CONTEXT.md` | evergreen | raiz do módulo | sim | glossário + fronteiras do módulo |
| `reference` | evergreen | `{módulo}/docs/reference/` | sim | consultar fatos (padrões, endpoints, infra) |
| `how-to` | evergreen | `{módulo}/docs/how-to/` | sim | executar uma tarefa |
| `explanation` | evergreen | `{módulo}/docs/explanation/` | sim | entender a visão/arquitetura de hoje |
| `ADR` | datado | `{módulo}/docs/adr/` | nunca | saber por que decidimos (numeração por módulo) |
| `spec` | datado | `{módulo}/docs/specs/` | nunca | o que uma entrega atendeu (referencia a Regra) |
| `plan` | datado | `{módulo}/docs/plans/` | nunca | o plano de uma entrega |

> **Divergência consciente do he4rt:** não há `prd` no repo. O requisito de produto
> vive **só** como Regra no portal central (ver [ADR-0003](adr/0003-doc-produto-regra-central-spec-repo.md));
> a `spec` referencia a Regra. Isso evita duplicar o requisito em dois lugares.

Docs **cross-module / system-wide** repetem a mesma estrutura em `/docs` na raiz do
repo (convenção he4rt).

## Front-matter (he4rt) e o link com o central

`type · title · module · status · date · author · related`

O campo `related` amarra a doc ao restante do grafo:

```yaml
related:
  regra: PROD-12@v2.0      # Regra no portal central (chave de junção)
  story: RPQ-STORY-123     # task no Monday (projeção)
```
