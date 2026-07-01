# Frescor e disposição — o que acontece com o documento ao longo do tempo

> **Foco.** Este é o achado **dominante** da pesquisa, e o mais alinhado com
> "problemas que a modelagem atual pode trazer no futuro". O desenho foi feito com
> capricho para **criar** e **usar** documentos, mas quase nada acontece quando um
> documento **envelhece, é substituído ou deveria sair de cena**. O problema não é só
> "o doc está velho" — é o **volume de ruído** que se acumula e passa a competir com o
> conteúdo bom, inclusive na recuperação por IA.

## O que já está certo (não mexer)

- **Supersessão já é tipada:** `substitui` ↔ `substituida_por`, casado com
  `status: obsoleto`, é exatamente o padrão da disciplina de records. Ver
  [taxonomia.md](../../taxonomia.md).
- **O transporte por snapshot idempotente está pronto** para receber regra de
  disposição — falta só a regra. Ver
  [Federação por PUSH](../../adr/0009-federacao-por-push-modulo.md).

---

## 1. `obsoleto` não faz nada — é um estado terminal morto · severidade alta

**O problema.** O ciclo de vida de uma Entrada termina em `obsoleto`, que hoje é só um
**selo**. Nada sai do índice da IA, nada sai do ranking para humanos, e a pilha de
versões antigas só cresce. A palavra "arquivar" não existe no desenho: um documento ou
"continua presente" ou "foi deletado do git" — não há meio-termo. Construímos a metade
da máquina de estados (o estado final), mas falta a **transição de saída**.

**Antes (hoje)** — `obsoleto` é um beco sem saída:

```
[rascunho] ──► [revisão] ──► [publicado] ──► [obsoleto]
                                              (vira selo e para aqui;
                                               continua no índice e na busca)
```

**Depois (proposto)** — `obsoleto` dispara uma **ação determinística**:

```
                                          ┌─ fora do índice da IA (por padrão)
[publicado] ──substituído──► [obsoleto] ──┤
                                          └─ recolhido atrás de "mostrar arquivados"
                                             na vitrine humana
       (versões antigas do PRD: só a última publicada na busca ativa;
        v1.0/v1.1 viram registro permanente, achável por id, fora do índice)
```

**Solução proposta.** Acoplar uma ação ao estado terminal, **sem** criar burocracia de
aprovação (a governança continua social): `status=obsoleto` ⇒ (a) excluído do índice da
IA por padrão; (b) recolhido atrás de "mostrar arquivados" para humanos. Para o PRD: a
busca ativa serve só a última versão publicada; as anteriores continuam auditáveis por
`RPQ:PRD-12@v1.0`, mas **fora** do índice padrão.

**Por que importa.** Uma versão revogada de PRD não é "doc velho inofensivo" — é uma
**contradição direta** da verdade atual. Para uma IA, ela vira uma resposta confiante e
errada, não uma falha óbvia.

**Decisão:** _pendente_

---

## 2. Não há classe de valor/retenção · severidade média

**O problema.** Tudo é tratado com o mesmo peso. Não distinguimos um documento que é
**registro de valor** (PRD congelado, ADR, spec — coisas de contrato/auditoria) de um
**transitório** (rascunho, nota). A distinção evergreen/datado que já existe é sobre
**edição**, não sobre **valor de retenção** — são eixos diferentes que estão confundidos.

**Antes (hoje)**

```
classe de FORMATO:  evergreen | datado   ← fala de "edita ou congela"
(não há)            classe de VALOR       ← "isto é registro de contrato ou descartável?"
```

**Depois (proposto)**

```yaml
classe_de_retencao: record | transitorio   # vocabulário curto
gatilho_revisao:    "inativo há N meses" | "substituído" | "nunca"
# record = protegido contra deleção silenciosa (ver ponto 5)
```

**Solução proposta.** Adicionar `classe_de_retencao` (só dois valores: `record` /
`transitório`) e um `gatilho_revisao` por tipo. Não precisa de regras complexas:
2–3 "baldes" já fecham o ciclo de vida (é o mínimo viável que a literatura de records
chama de *big-bucket retention*).

**Por que importa.** Sem uma classe de valor, o sistema não tem como saber o que
deveria sair, quando, nem por quê — e o corpus só cresce.

**Decisão:** _pendente_

---

## 3. Não existe "revisado em" — o frescor depende da disciplina de cada um · severidade alta

**O problema.** O gatilho de publicação é manual; o selo de "velho" é passivo e sem
dono; a data de revisão (`revisao_ate`) foi descartada de propósito. `atualizado_em`
registra "alguém mexeu", não "alguém conferiu que ainda está certo". Um
`status: publicado` de dois anos atrás é **indistinguível** de um conferido ontem. E
`owner` é o e-mail de uma pessoa — uma bomba-relógio quando ela sai da empresa.

**Antes (hoje)**

```yaml
atualizado_em: 2024-03-10   # git mexeu (pode ter sido um typo)
owner: ana@empresa.com      # pessoa; quebra quando ela sai
status: publicado           # sinal social, sem registro de quem/quando promoveu
# (não há) revisado_em
```

**Depois (proposto)**

```yaml
atualizado_em: 2024-03-10        # automático (git)
revisado_em:   2026-05-01        # MANUAL: alguém conferiu que ainda vale
owner: time-pagamentos           # referência a Time/Pessoa, não e-mail solto
# TTL de revisão POR TIPO: ADR/decisão = longo; processo = semestral;
#                          referência/how-to = a cada release
# varredura notifica o owner quando o TTL estoura  → GATILHO, não trava
```

**Solução proposta.** Separar `revisado_em` (humano, manual) de `atualizado_em`
(automático); mostrar "última revisão por {owner} em DD/MM"; definir um TTL de revisão
por tipo e uma varredura que **avisa** o dono quando vence (gatilho, nunca um portão
que bloqueia); e tornar `owner` uma referência a Time/Pessoa.

**Por que importa.** O documento que apodrece em silêncio vira erro confiante — e ele
é a fonte de specs, ADRs e código. O que enche o corpus de lixo é o **esquecimento**,
não a má-fé.

**Decisão:** _pendente_

---

## 4. A pilha de versões do PRD só cresce e compete na busca · severidade alta

**O problema.** O PRD aponta para uma pilha de versões congeladas, e isso está certo
como **histórico**. O problema é que **todas** continuam disponíveis na recuperação
ativa. Quanto mais o produto evolui, mais versões revogadas há concorrendo com a
verdade atual.

**Antes / Depois** — mesma pilha, recuperação diferente:

```
PRD-12:  v1.0   v1.1   v2.0   v3.0(atual)
ANTES:   [busca]  [busca]  [busca]  [busca]    ← todas competem
DEPOIS:   hist.    hist.    hist.   [busca]     ← só a atual; resto auditável por @vX
```

**Solução proposta.** A recuperação padrão (humana e de IA) serve só a última versão
publicada; as anteriores ficam acessíveis por referência explícita à versão
(`RPQ:PRD-12@v1.0`), mas fora do índice padrão. É o mesmo mecanismo do ponto 1.

**Decisão:** _pendente_

---

## 5. Deleção propaga sem rede de proteção · severidade alta

**O problema.** "Snapshot completo, deleção propaga" é a operação mais destrutiva do
sistema e é o **comportamento padrão**. Um `main` com a pasta `docs/` apagada por
engano, seguido de um `docs:publish`, **zera o espelho** num único POST — incluindo
PRDs congelados, ADRs e specs de valor de contrato. Não há lixeira, confirmação,
limiar nem versão recuperável. Ver
[Federação por PUSH](../../adr/0009-federacao-por-push-modulo.md).

> Este ponto tem **duas leituras**. A leitura de **segurança** (token vazado como vetor
> de ataque) está em [Segurança](6-seguranca.md). Aqui tratamos a leitura de **perda de
> dados operacional**: mesmo sem nenhum atacante, um erro humano destrói registros.

**Antes (hoje)**

```
snapshot recebido ──► o que sumiu do snapshot é DELETADO do espelho
                      (sem lixeira, sem limiar, sem confirmação, sem rollback)
```

**Depois (proposto)**

```
snapshot recebido ──► some do snapshot? ──► marca "arquivada/órfã" (soft-delete)
                          │
                          └─ remoção acima de um limiar (>30% ou tudo)?
                                  └─► exige confirmação fora-de-banda
                      snapshot recebido é versionado → rollback possível
                      Entrada classe `record` → preservada com log
```

**Solução proposta.** (1) *Soft-delete*: o que some do snapshot vira "arquivada", não
apagada; (2) *circuit-breaker*: remoção acima de um limiar pede confirmação; (3)
versionar o snapshot recebido para permitir rollback; (4) proteger Entradas `record`
(usa a classe do ponto 2).

**Por que importa.** O transporte idempotente é ótimo — mas hoje a operação default é
"destruir tudo o que não veio". Uma rede de proteção mínima evita um apagão irreversível.

**Decisão:** _pendente_

---

## Resumo para triagem

| # | Ponto | Severidade |
|---|---|---|
| 1 | `obsoleto` não dispara nenhuma ação | alta |
| 2 | Sem classe de valor/retenção (`record` vs transitório) | média |
| 3 | Sem "revisado em"; frescor depende de disciplina | alta |
| 4 | Pilha de versões do PRD compete na busca | alta |
| 5 | Deleção propaga sem soft-delete / circuit-breaker | alta |

Os pontos 1, 2, 4 e 5 se encaixam: uma classe `record` + uma ação no `obsoleto` +
recuperação só da versão corrente + soft-delete formam **um único ADR de "disposição"**.
Fontes (records management, doc rot): [fontes.md](../fontes.md).
