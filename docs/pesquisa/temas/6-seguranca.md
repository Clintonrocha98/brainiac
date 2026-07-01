# Segurança — modelagem de ameaça

> **⏸ Fora de escopo agora.** O Brainiac é um projeto **interno**, então você optou
> por não tratar segurança nesta fase. O conteúdo da pesquisa fica **preservado aqui**
> para quando fizer sentido (por exemplo, se algum dia houver público "externo" ou
> exposição fora da rede interna). Nada aqui precisa ser decidido agora.

> Há **um ponto com leitura dupla**: "deleção propaga" também é um risco de **perda de
> dados operacional** (erro humano, sem atacante nenhum) — e essa leitura **não** está
> adiada; está no tema [Frescor e disposição](2-frescor-e-disposicao.md). Aqui fica só
> a leitura de ameaça.

## O fio condutor

A pesquisa resume: **segurança foi tratada como checkbox, não como modelagem de
ameaça**. Nenhum registro de decisão escreve "o que pode dar errado" nem "como
validamos". A costura mais sensível — `repo de cliente → webhook → espelho` — nunca foi
desenhada como fronteira de confiança. As escolhas (push, iframe isolado) estão certas
no princípio; falta a disciplina de ameaça em volta delas.

---

## 1. Não há modelo de ameaça em nenhum ADR

**O problema.** Zero seção de ameaças, zero diagrama de fluxo de dados, zero análise do
tipo STRIDE. O webhook único resolve **um** risco (evitar um token que lê toda a org),
mas deixa vários outros sem tratamento: sem proteção contra repetição de requisição
(replay), sem amarração entre o token e a sigla do projeto, sem trilha de auditoria,
sem limite de taxa.

**Depois (proposto)** — uma tabela STRIDE por elemento do webhook de ingestão, e, em
concreto:

```
- assinar timestamp + janela curta (anti-replay)
- nonce / request-id com TTL
- amarrar token ↔ sigla no servidor (o token de um projeto não sobrescreve outro)
- comparação de assinatura em tempo constante (hash_equals)
- registrar (sigla, commit, autor, timestamp) por publish
- limite de taxa + limite de tamanho do payload
- rotação / revogação / expiração do secret por projeto
```

---

## 2. Stored-XSS via markdown — a defesa é só a palavra "sanitização"

**O problema.** O Brainiac é o **único** renderizador de markdown vindo de N repos. O
markdown de origem é entrada **semi-confiável** (autor não-técnico cola conteúdo; TI
publica do repo). O CommonMark, por padrão, deixa passar HTML cru e URLs `javascript:`.
Um `<img onerror=...>` ou `[x](javascript:...)` vira código executando na sessão de
qualquer leitor. Ver [Markdown canônico, render centralizado](../../adr/0010-markdown-canonico-render-centralizado.md),
que trata o markdown como "fonte limpa para busca", nunca como superfície de ataque.

**Depois (proposto).** Nomear o sanitizador (ex.: HTMLPurifier sobre o HTML de saída);
bloquear HTML inline perigoso e URLs `javascript:`/`data:` (`allow_unsafe_links=false`);
declarar um `securityLevel` restrito no mermaid; e manter uma suíte de regressão com
*payloads* de XSS conhecidos.

---

## 3. Deleção propaga (leitura de ameaça)

**O problema.** Um token vazado habilita destruição total: um POST zera o espelho. É a
combinação de *spoofing* (token vazado) com negação de serviço/destruição.

**Depois (proposto).** As mesmas defesas operacionais do tema de disposição
(soft-delete, circuit-breaker, snapshot versionado) — aqui motivadas pela ameaça. Mais
a amarração token ↔ sigla do ponto 1.

---

## 4. O iframe do Artefato está certo, mas sem invariante testável

**O problema.** Isolar o Artefato num iframe de origem separada **sem**
`allow-same-origin` é a decisão correta. Ver
[Artefato como asset HTML por link](../../adr/0012-artefato-asset-html-por-link-iframe-isolado.md).
Mas: (a) o conjunto de flags não está fixado como invariante; (b) o host externo
(waifuvault) é multi-inquilino — "origem isolada" vale por host, não por dono, então um
segundo artefato no mesmo host é "mesma origem" e pode ler o storage do outro; (c) não
há CSP declarada nem nada que impeça o artefato de fazer requisições para fora
(exfiltração).

**Depois (proposto).** Fixar as flags como invariante ("`sandbox=allow-scripts`; jamais
`allow-same-origin`"); reconhecer que o host externo não isola artefatos entre si (risco
aceito **ou** store próprio com origem por artefato); declarar CSP
(`frame-ancestors 'self'`); e adicionar uma seção "como validamos".

---

## 5. O módulo de federação é cadeia de suprimentos única

**O problema.** O módulo de doc instalado em todo projeto é um ponto único: se ele for
comprometido, alcança todos os projetos. Não há SBOM (lista de dependências/permissões),
proveniência de release (assinatura, pinning por hash) nem "raio de alcance se
comprometido".

**Depois (proposto).** SBOM do módulo, proveniência das releases e processo de rotação
de secret.

---

## Resumo (para quando o tema entrar)

| # | Ponto | Severidade |
|---|---|---|
| 1 | Sem modelo de ameaça (STRIDE) no webhook | alta |
| 2 | Stored-XSS via markdown | alta |
| 3 | Deleção propaga (leitura de ameaça) | alta |
| 4 | iframe sandbox sem invariante/CSP | média |
| 5 | Módulo como cadeia de suprimentos única | média |

**Decisão:** _fora de escopo agora (Brainiac interno) — reavaliar se houver exposição externa_

Fontes (STRIDE, OWASP, Shostack, SLSA/SBOM): [fontes.md](../fontes.md).
