# Formatos de documentação — contexto TI (por repo)

Co-localizado no repositório (ver [ADR-0002](adr/0002-topologia-hibrida.md)).
Alinhado à guideline he4rt + uma camada evergreen para os docs duráveis que o
he4rt sozinho deixa sem lar.

## As duas classes

- **Evergreen** — você **edita** o mesmo doc para refletir o estado atual. Existe
  **um** por assunto; o histórico fica no git (implícito).
- **Datado (append-only)** — cada doc é **congelado** num momento; **nunca** é
  editado. Quando algo muda, cria-se um **novo**. Leem-se em conjunto.

## Formatos

| Formato | Classe | Onde vive | Edita? | Lê para… |
|---|---|---|---|---|
| `README.md` | evergreen | raiz do módulo | sim | entrar no módulo (entrada prática + roadmap) |
| `CONTEXT.md` | evergreen | raiz do módulo | sim | glossário + fronteiras do módulo |
| `reference` | evergreen | `{módulo}/docs/reference/` | sim | consultar fatos (padrões, endpoints, infra) |
| `how-to` | evergreen | `{módulo}/docs/how-to/` | sim | executar uma tarefa |
| `explanation` | evergreen | `{módulo}/docs/explanation/` | sim | entender a visão/arquitetura de hoje |
| `ADR` | datado | `{módulo}/docs/adr/` | nunca | saber por que decidimos (numeração por módulo) |
| `spec` | datado | `{módulo}/docs/specs/` | nunca | o que uma entrega atendeu (referencia o PRD) |
| `plan` | datado | `{módulo}/docs/plans/` | nunca | o plano de uma entrega |

> **Divergência consciente do he4rt:** não há `prd` no repo. O requisito de produto
> vive **só** como PRD no Brainiac (ver [ADR-0003](adr/0003-doc-produto-prd-spec-repo.md));
> a `spec` referencia o PRD. Isso evita duplicar o requisito em dois lugares.

Docs **cross-module / system-wide** repetem a mesma estrutura em `/docs` na raiz do
repo (convenção he4rt) e levam `module: global`.

## Front-matter (he4rt) e o link com o Brainiac

`type · title · module · status · date · author · related`

O `module` marca o **escopo** da Entrada: o nome do módulo (ex.: `pagamentos`) ou
`global` quando a doc é do projeto inteiro (a de `/docs` na raiz). Obrigatório no TI
— é o que distingue o README/ADR/spec **de um módulo** do **global**.

O campo `related` amarra a doc ao restante do grafo:

```yaml
related:
  prd: RPQ:PRD-12@v2.0     # PRD no Brainiac (chave de junção)
  story: RPQ-STORY-123     # task no Monday (projeção)
```
