# Tipos de documento — como classificamos cada documento

> **Foco.** Este é o tema mais próximo do objetivo "melhorar a modelagem dos
> diferentes tipos de documento". Reúne os pontos da pesquisa sobre **como uma
> Entrada é classificada**: o propósito, o dono, e o ciclo do PRD enquanto formato.
> Cada ponto tem: o problema em linguagem simples · antes/depois · solução · decisão.

## O que já está certo (não mexer)

- **Classificar pelo propósito do documento, não pelo departamento.** É decisão-âncora
  e a literatura inteira converge nela (Diátaxis, DITA, arquitetura da informação).
  Ver [Taxonomia de documentação orientada a propósito](../../adr/0001-taxonomia-orientada-a-proposito.md).
- **Já separamos _Propósito_ de _Formato_.** "Propósito" é o conhecimento que o
  documento entrega; "Formato" é a espécie concreta (README, ADR, spec, PRD…). Essa
  separação, feita no polimento anterior, **já responde à principal recomendação
  estrutural da pesquisa** ("separar o modo do formato"). Ver o glossário em
  [CONTEXT.md](../../../CONTEXT.md).

Os pontos abaixo são o que **sobrou** depois dessa separação.

---

## 1. Os cinco propósitos se sobrepõem (não são exclusivos) · severidade alta

**O problema.** Hoje dizemos que todo documento tem **exatamente um** dos cinco
propósitos e que eles não se misturam. Mas três deles não estão no mesmo nível dos
outros:

- **`decisão`** é, na verdade, um caso de **`explicação`** — um ADR explica *por que*
  algo foi feito. "Decisão" parece mais o **formato** (ADR) do que um propósito à parte.
- **`processo`** é "o fluxo de uma área". Isso é mais *de quem é* o documento
  (departamento) do que *que conhecimento ele entrega*. Um processo que se executa é
  um **how-to**; a justificativa de um handoff é **explicação**.
- Falta o modo **`tutorial`** — aprender fazendo, mão na massa (diferente de how-to,
  que é executar uma tarefa que você já sabe que precisa fazer). É justamente o que o
  onboarding pede — e o onboarding já existe no desenho como **Coleção**.

E o próprio repositório já dá a prova de que a exclusividade não se sustenta: o
**ATRITO 1** dos exemplos mostra que o README de um módulo é, ao mesmo tempo,
referência (contratos) + how-to (como rodar) + explicação (por que idempotência).

**Antes (hoje)** — uma única lista plana de cinco valores, ditos exclusivos:

```
proposito: referencia | how-to | explicacao | decisao | processo
           (1 valor, "mutuamente exclusivo")
```

**Depois (uma direção possível)** — distinguir o **modo** (o que o leitor quer:
aprender / fazer / consultar / entender) do **formato** (que já é faceta à parte):

```
MODO (o que o leitor precisa)      FORMATO (a espécie concreta — já é faceta)
  tutorial   (aprender fazendo)      README · CONTEXT · reference · how-to
  how-to     (executar tarefa)       explanation · ADR · spec · plan · PRD
  referencia (consultar fato)
  explicacao (entender o porquê)     "decisão" deixa de ser propósito:
                                      vira explicação + formato=ADR
                                     "processo" deixa de ser propósito:
                                      executável → how-to
                                      justificativa/handoff → explicação
                                      agrupamento → Coleção por departamento
```

**Solução proposta.** Colapsar `decisão` em `explicação` (a "decisão-ness" já é
capturada pelo formato ADR); eliminar `processo` redistribuindo-o; e **decidir
conscientemente** sobre `tutorial`: ou adicioná-lo, ou declarar por escrito que a
fusão tutorial/how-to é uma escolha pragmática nossa (a própria Diátaxis discute
quando essa fusão é defensável). Por fim, escrever a tabela canônica Formato → Modo.

**Por que importa.** Classificação inconsistente na origem degrada silenciosamente
toda a recuperação por faceta; e sem um modo "aprender", o onboarding fica sem o
encaixe natural.

**Decisão:** ✅ aplicado. Enum final = `referencia` · `how-to` · `explicacao`.
`decisão` colapsou em `explicação` (a "decisão-ness" fica no formato ADR);
`processo` foi redistribuído (executável → how-to; justificativa/handoff →
explicação; agrupamento por área → Coleção); `tutorial` **não** vira propósito —
how-to absorve "aprender fazendo" (fusão declarada). Refletido em `taxonomia.md`,
`CONTEXT.md`, `adr/0001` e `exemplos-entradas.md`.

---

## 2. `departamento` aceita só um valor · severidade média

**O problema.** A faceta `departamento` é "a Área que produz e mantém a Entrada — o
dono. Uma só por Entrada." Ela mistura dois conceitos diferentes num campo só:

- **quem é dono** (responsabilidade editorial), e
- **sobre que área o documento fala** (assunto).

Documentos cross-área não cabem: uma **Visão de produto** é de Produto *e* Negócio;
um **handoff** cruza times; um doc de **arquitetura** é TI *e* Produto. Forçar um valor
só recria, dentro da faceta, o silo que a taxonomia tentou eliminar. E há uma
assimetria sem razão: `publico_alvo` e `projeto` já são multi-valor — só
`departamento` é travado em um.

**Antes (hoje)**

```yaml
departamento: enum Área (1)   # dono E assunto, no mesmo campo
publico_alvo: enum Área (N)   # já multi-valor
projeto:      enum Projeto (N) # já multi-valor
```

**Depois (proposto)** — separar os dois conceitos:

```yaml
owner_area:       enum Área (1)   # responsabilidade editorial (o dono)
areas_de_assunto: enum Área (N)   # sobre que áreas o documento fala
```

**Solução proposta.** Primeiro **checar** se isto não é só o `publico_alvo` por outro
nome (talvez "também relevante para" já esteja resolvido). Se forem mesmo co-donos,
separar `owner_area` (1) de `areas_de_assunto` (N). A *mecânica* de mudar a
cardinalidade com segurança está no tema [Evolução e migração](3-evolucao-e-migracao.md).

**Por que importa.** Sem isso, conteúdo legitimamente cross-área é forçado a "escolher
um lado", e some das buscas da outra área.

**Decisão:** ✅ resolvido — **manter como está**. `departamento` continua dono
**único** (1 valor); a assimetria com `publico_alvo`/`projeto` reflete o papel, não é
defeito. **Não** criar `areas_de_assunto`: o cross-área real é coberto pelo
`publico_alvo` (multi-valor), e o padrão dominante é a doc interna de departamento
(dono = assunto = público = uma área). O `publico_alvo` foi reesclarecido como sinal
de **relevância, não de acesso** — toda Entrada é visível à empresa (Brainiac interno).
Refletido em `CONTEXT.md` e `exemplos-entradas.md`.

---

## 3. O PRD só tem dois estados: "rascunho" e "congelado" · severidade média

**O problema.** Hoje qualquer mudança no texto de um PRD publicado vira uma **versão
nova**, e o Produto declara se é menor ou maior. A correção de um *typo* foi um
caso explicitamente decidido: vira versão menor. Ver
[Ciclo de vida do PRD: o texto versiona e congela ao publicar](../../adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md).

A pesquisa questiona esse binário:

- Um *typo* que vira versão **empilha ruído** e dispara ao TI o sinal "o contrato
  mudou" sem que nada tenha mudado de fato. Com o tempo, treina o TI a **ignorar** os
  avisos de versão (efeito "o menino que gritava lobo").
- Não há **estado provisório** nem **janela de objeção** antes de um PRD virar a base
  de uma spec — ele é congelado por uma pessoa só.

**Antes (hoje)**

```
rascunho ──publicar──► congelado
                         (qualquer texto novo = versão nova; até typo)
```

**Depois (proposto)** — um terceiro caminho para correção sem ruído + uma janela curta:

```
rascunho ──publicar──► congelado ──errata──► congelado + errata anexada
                         │                    (não incrementa versão,
                         │                     não dispara sinal ao TI)
                         └──muda intenção──► versão nova (regra de ouro IETF)

publicar de uma major:  [diff do texto exibido] → janela de objeção curta → vira base de spec
```

**Solução proposta.** Errata anexada à versão congelada (corrige sem versionar e sem
sinalizar); diff textual da última versão no momento de publicar; e uma janela de
objeção leve antes de a major virar base de spec. A tradição RFC/PEP resolveu
exatamente isto com mecanismos separados (errata vs. nova versão).

**Atenção.** Isto **reabre uma decisão já tomada** — o ciclo de vida atual rejeitou
a errata de propósito, "para ser mais simples e honesto". A pesquisa argumenta que o
custo do ruído supera essa simplicidade. É um trade-off para você bater o martelo.

**Decisão:** ✅ resolvido — **manter o ciclo de vida atual** (congelar sempre; sem
errata; sem gate). O "simples e honesto" vence: nenhuma mudança escapa sem rastro, e o
boundary "é pequeno o suficiente?" é a complexidade que se evita. Dois ajustes
extraídos da pesquisa, sem reverter a decisão: (1) a versão **menor é silenciosa** — só
a maior aciona o caminho de Spec; (2) **diff entre versões** como recurso de leitura
(visibilidade, não gate), para a fase de código. Janela de objeção formal **rejeitada**
(vira gate; já coberta pelo rascunho legível + revisão do TI antes da Spec). Refletido
em `adr/0011`.

---

## 4. Regra de negócio cross-feature é copiada em vários PRDs · severidade média

**O problema.** Uma regra de negócio é uma **seção dentro do PRD**. Ver
[PRD é a unidade central de produto](../../adr/0007-prd-unidade-central-de-produto.md).
Quando a mesma regra vale para várias features, ela acaba **copiada** em N PRDs. Para
mudá-la, é preciso editar N documentos — e, como cada versão de PRD é congelada,
multiplica por versão. A Coleção agrupa **links**, não conteúdo, então não resolve isso.

Note a inconsistência: nós **já praticamos** fonte-única no eixo PRD → spec (a spec
referencia o PRD, não copia), mas **abandonamos** no eixo regra → N-PRDs.

**Antes (hoje)**

```
Regra "voucher é de uso único"
   ├─ copiada na seção 4 do PRD-12
   ├─ copiada na seção 2 do PRD-19   ← mudar a regra = editar 3 lugares
   └─ copiada na seção 7 do PRD-31      (× versões congeladas)
```

**Depois (proposto)** — regra vira Entrada de 1ª classe, referenciada por chave:

```
Regra "voucher é de uso único"  →  RPQ:RN-07  (uma Entrada, uma fonte)
   ├─ PRD-12 §4:  {{ ref: RPQ:RN-07 }}   ← resolvido no render
   ├─ PRD-19 §2:  {{ ref: RPQ:RN-07@v3 }} ← PRD congelado fixa a versão da regra
   └─ PRD-31 §7:  {{ ref: RPQ:RN-07 }}
```

**Solução proposta.** Promover a regra cross-feature a Entrada própria e transcluir
por chave no momento da leitura; o PRD congelado **fixa a versão** da regra que
materializou (`@v3`), para não deixar de ser congelado.

**Atenção.** A unidade central de produto **já previu** isto e **adiou**: extrair a
regra "sob demanda, se ela provar ser cross-feature". A pesquisa concorda com extrair
sob demanda, mas pede que a **mecânica de transclusão** exista antes de a primeira
regra cross-feature aparecer.

**Decisão:** ✅ resolvido — **manter adiado** (extrai sob demanda, ADR-0007), com uma
**anotação** registrando o caminho previsto: transclusão por referência (regra vira
Entrada `RPQ:RN-07`; PRD inclui por `{{ ref: RPQ:RN-07 }}`; versão fixada ao congelar).
A mecânica **não** se constrói agora — a nota só evita que o render e o esquema de ids
fechem a porta. O congelamento já neutraliza o pior (mudança de regra propaga como
novas versões de qualquer forma). Registrado em `adr/0007`.

---

## Resumo para triagem

| # | Ponto | Severidade | Reabre decisão? |
|---|---|---|---|
| 1 | ✅ Os cinco propósitos se sobrepõem | alta | não (refina a taxonomia de propósito) |
| 2 | ✅ `departamento` aceita só um valor | média | não |
| 3 | ✅ PRD só tem rascunho/congelado | média | reaberta, **mantida** |
| 4 | ✅ Regra cross-feature copiada em N PRDs | média | não (mantém adiado + nota) |

Fontes e leituras por trás destes pontos: [fontes.md](../fontes.md) (Diátaxis, DITA,
arquitetura da informação, RFC/PEP).
