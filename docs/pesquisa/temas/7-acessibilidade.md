# Acessibilidade

> **⏸ Fora de escopo agora.** Como o Brainiac é **interno**, você optou por não tratar
> acessibilidade nesta fase. O conteúdo da pesquisa fica **preservado aqui** para quando
> fizer sentido (sobretudo se algum dia houver público "externo", onde há exposição
> legal — EAA / EN 301 549). Nada aqui precisa ser decidido agora.

## O fio condutor

A acessibilidade é uma **propriedade estrutural** que o schema e o render poderiam
garantir — e hoje não há um único campo dela. O padrão se repete dos outros temas: o
ponto de controle existe (ingest determinístico + render único), mas a invariante não
foi escrita. Como o ingest determinístico e o render centralizado são exatamente a
alavanca de "resolver na origem", a oportunidade fica desperdiçada.

---

## 1. Nenhum campo de acessibilidade no schema · severidade alta (quando entrar)

**O problema.** Uma Entrada **só-artefato** — caso comum em Design e Marketing — pode
ser 100% inacessível a leitor de tela ou teclado, sem nenhum equivalente textual e sem
nada que o exija. Os diagramas mermaid não têm descrição; o iframe do Artefato não tem
`title`. Ver [Artefato como asset HTML por link](../../adr/0012-artefato-asset-html-por-link-iframe-isolado.md).

**Antes / Depois**

```
ANTES:   Entrada só-artefato = HTML opaco, sem equivalente textual
         mermaid sem descrição · iframe sem title · sem campo de idioma
DEPOIS:  descricao_acessivel obrigatória na Entrada só-artefato
         title no iframe · mermaid sem accDescr = aviso no ingest
         idioma no core (default pt-BR → lang=)
         render garante saída semântica (um <h1>, hierarquia sem saltos, landmarks)
```

**Solução proposta.** Tratar acessibilidade como invariante de schema e de render:
campo `descricao_acessivel` obrigatório em Entrada só-artefato; `idioma` no metadado
core; o mesmo parser que já deriva "tem mermaid" emite aviso quando falta descrição; e o
render centralizado garante HTML semântico. Verificação automática (axe-core/pa11y) na
fase de código.

---

## 2. Acessibilidade cognitiva (linguagem simples) · severidade baixa

**O problema.** Os próprios ADRs são densos, com jargão ("idempotente", "HMAC",
"render-on-read memoizado") e parágrafos longos. O público inclui não-técnicos e
liderança. O campo `resumo` é o único gesto nessa direção, mas serve a preview/IA, não a
uma política de redação.

**Solução proposta.** Linguagem simples como princípio de autoria (voz ativa, frases
curtas, glossário obrigatório quando o público inclui não-TI); reaproveitar o `resumo`
como um resumo de leitura fácil; usar o glossário como camada de "defina o jargão" no
render.

---

## Resumo (para quando o tema entrar)

| # | Ponto | Severidade |
|---|---|---|
| 1 | Sem campos de acessibilidade no schema/render | alta |
| 2 | Linguagem densa, sem política de redação simples | baixa |

**Decisão:** _fora de escopo agora (Brainiac interno) — reavaliar se houver público externo_

Fontes (WCAG, plain language, mermaid a11y): [fontes.md](../fontes.md).
