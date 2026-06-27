# Arquitetura de Documentação da Empresa

Visão consolidada (sessões 1 e 2): como a documentação da empresa é organizada,
onde vive, quais os tipos e como é criada — por humanos e por IA.

- Glossário canônico: [CONTEXT.md](../CONTEXT.md)
- Decisões: [docs/adr/](adr/)
- Detalhes: [taxonomia.md](taxonomia.md) (schema central) · [tipos-ti.md](tipos-ti.md) (tipos de TI)

---

## 1. O problema

Empresa crescendo, processos de TI não documentados, sem handbook. Docs hoje são
READMEs técnicos por módulo + artefatos (HTML) trocados peer-to-peer no waifuvault,
sem listagem nem contexto. Fora de TI (Produto, Marketing, Negócio) também precisa
documentar — mas é menos técnico. E quer-se que tudo carregue metadados para a IA
classificar e recuperar.

**A tese:** o que resolve isso não é escrever mais doc — é a **infraestrutura**:
um modelo único onde cada doc é uma Entrada classificada, achável por humano e IA,
independente do departamento.

---

## 2. Princípios (as decisões-âncora)

| Princípio | ADR |
|---|---|
| Organizar por **tipo/propósito**, não por departamento (departamento é faceta) | [0001](adr/0001-taxonomia-orientada-a-proposito.md) |
| **Dois andares**: produto nasce no Brainiac (verdade do PRD) + TI no repo (verdade do código); spec/ADR derivadas do PRD | [0002](adr/0002-topologia-hibrida-pull.md) |
| Produto: **PRD** versionado no Brainiac (grão de feature) + **Spec** datada no repo | [0003](adr/0003-doc-produto-regra-central-spec-repo.md) · [0007](adr/0007-prd-unidade-central-de-produto.md) |
| **Documentação é upstream** do rastreador (Monday é projeção); id do PRD = chave | [0004](adr/0004-doc-upstream-do-rastreador.md) |
| **Todo autor** escreve por **IA (guideline)** — a IA preenche o front-matter; ingest **determinístico** | [0005](adr/0005-autoria-nao-tecnico-guideline-paste.md) |

---

## 3. Topologia — os dois andares

```
  ANDAR TI  (por repo · dev-first · FONTE DA VERDADE DO CÓDIGO)
  ┌──────────────────────┐      ┌──────────────────────┐
  │ Repo: pagamentos     │      │ Repo: faturamento    │
  │ docs co-localizadas  │      │ docs co-localizadas  │
  │ (md + front-matter)  │      │ (md + front-matter)  │
  │  `docs:publish` ─────┼──┐   │  `docs:publish` ─────┼──┐
  └──────────────────────┘  │   └──────────────────────┘  │
                            │ PUSH (módulo empurra)       │ PUSH
                            ▼                             ▼
            ┌───────────────────────────────────────────────┐
            │   ANDAR EMPRESA  (Brainiac)                   │
            │   • nasce o PRD — fonte da verdade do produto │
            │   • recebe e espelha docs de TI (push)        │
            │   • hospeda docs não-técnicas (Produto…)      │
            │   • mantém o grafo de links produto↔código    │
            │   • porta única p/ liderança e Produto        │
            └───────────────────────────────────────────────┘
```

**Federar** = o módulo de cada repo **empurra** (push) a doc para o Brainiac, que a
**espelha** (metadado + **markdown-fonte**; quem renderiza é o Brainiac — [ADR-0010](adr/0010-markdown-canonico-render-centralizado.md)); o git segue a fonte da verdade. O
código nunca sai do repo — **só a doc**. O **requisito de produto** nasce no
Brainiac (o **PRD**, fonte da verdade do produto); a **spec/ADR** no repo é
**derivada do PRD** — a direção é sempre PRD → spec, nunca o contrário.

---

## 4. Tipos de documento

Dicionário **compartilhado** por todos os departamentos (how-to é how-to em
qualquer área). Cada tipo é de uma **classe**:

```
EVERGREEN  (você EDITA o mesmo doc · reflete o AGORA · 1 por assunto)
   README · CONTEXT · reference · how-to · explanation
   (PRD = versionado: última versão = verdade, guarda histórico)

DATADO / append-only  (CONGELADO num momento · nunca edita · novo a cada vez)
   ADR · spec · plan
```

Por andar:
- **TI (repo):** os 8 acima (+ README/CONTEXT/spec são exclusivos do repo).
- **Brainiac (Produto…):** reusa reference/how-to/explanation/decision + o **PRD**
  (versionado, grão de feature) + a **Visão de produto** (macro, 1 por Projeto).

> Divergência consciente do he4rt: **sem `prd` no repo** — o requisito vive só
> como **PRD** no Brainiac (ADR-0003 · 0007).

---

## 5. Modelo de dados — a Entrada e seus metadados

Metadado em **três camadas**:

```
ENTRADA
├─ CORE  (toda Entrada, todo depto, todo tipo)
│    id · titulo · resumo · tipo · departamento · publico_alvo
│    projeto · status · owner · datas · palavras_chave · related
├─ por TIPO   (ADR→deciders · PRD→versao)
└─ por DEPARTAMENTO  (TI→module · Marketing→canal · Produto→segmento)
```

Facetas de vocabulário controlado: `tipo`, `departamento`, `publico_alvo`,
`projeto`. Só `palavras_chave` é livre. Isso é o que faz a IA
filtrar e recuperar bem (e gerar o "Motivo" da recomendação no momento da query).

**Obsolescência:** sinalizada só por `status` (rascunho/revisão/publicado/
obsoleto) + supersede via `related` — sem data de revisão (sem anti-rot).

**Conteúdo:** guardado como **markdown** (canônico — nativo e espelho), renderizado
pelo Brainiac sob demanda; o HTML é cache ([ADR-0010](adr/0010-markdown-canonico-render-centralizado.md)).
O **PRD** não é uma linha que se sobrescreve: é uma Entrada que aponta para uma
**pilha de versões congeladas** ([ADR-0011](adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md)).

### 5.1 Projeto — contêiner e origem (ADR-0006)

Documentos são criados **sob uma Projeto**, entidade de 1ª classe:

```
PROJETO RPQ {
  nome_negocio: "Plataforma de Recrutamento"   (gestão / liderança)
  nome_tecnico: "recruit-party-quest"           (TI / repo)
  sigla:        "RPQ"                            (handle canônico)
  slug:         "recruit-party-quest"
}
```

A **sigla** alinha negócio ↔ TI ↔ rastreador ↔ catálogo e é a **"origem"** dos
ids qualificados: `RPQ:adr/0001`, `RPQ:PRD-12`, e o já-usado `RPQ-STORY-123` no
Monday. Cada origem é dona do seu id nativo; o catálogo só qualifica com a sigla.

---

## 6. Fluxo de produto — PRD ↔ Spec ↔ execução

O **PRD** (produto, Produto, Brainiac) tem grão de feature/grupo, é versionado e
contém as regras de negócio como seção. Cada major casa com uma **Spec**
(implementação, TI, repo):

```
PRD "Gestão de Vouchers" RPQ:PRD-12   (versionado · última = verdade)
  v1.0  ─────►  v2.0  ─────►  v3.0
   │             │             │      major = muda comportamento → gera spec
   ▼             ▼             ▼      minor = só texto → não gera spec
 SPEC          SPEC          SPEC
 (2026-01)     (2026-06)     (2026-09)   (datadas, congeladas, no repo)
```

O ciclo (cada doc tem 1 dono; o outro lado lê):

```
 ✏️ Produto edita ─► PRD RPQ:PRD-12 (Brainiac) ◄─ 👁️ liderança lê
        │
   TI lê o PRD 👁️  ─►  ⚙️ grill-me-with-docs  ─►  SPEC no repo ✏️
                                                   related:{ prd: RPQ:PRD-12@v2.0 }
        │                                                │
        └────  o módulo empurra a spec → Brainiac (PUSH)  ─┘
```

**Identidade (ADR-0004 + 0006):** o id do PRD (`RPQ:PRD-12`, qualificado pela
sigla do Projeto, cunhado pelo Brainiac) é a chave de junção. A spec aponta pra ele;
a task no Monday carrega ele. Monday é projeção descartável — trocável sem quebrar
nada.

### 6.1 Governança (ADR-0008)

`status` é um **sinal social**, não um gate de sistema — a plataforma não policia.

```
[rascunho] ─pronto─► [revisão] ─alinhado─► [publicado] ─substituído─► [obsoleto]
 Produto             legível;            Produto troca;
 escreve             TI+PO revisam       libera Spec/ADR (TI)
                     (reunião/msg)       + tasks no Monday (PO)
```

Qualquer um do Produto pode publicar; a corretude vem da disciplina do time.
Permissões podem ser adicionadas depois sem mudar o modelo de estados.

**Ciclo de vida e versão do PRD ([ADR-0011](adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md)):**
*salvar ≠ publicar* — salvar deixa um **rascunho legível** (já visível, ainda sem
valer); **publicar** é deliberado e **congela** a versão, virando a verdade. Só o
**texto** versiona; metadado (fora `status`) edita no lugar. Quem declara **menor**
(só texto, não gera spec) × **maior** (muda comportamento, gera spec) é o
**Produto**, no publish. Enquanto uma nova versão está em rascunho, a **última
publicada continua valendo** pro TI (e o rascunho fica disponível para leitura).

---

## 7. Autoria — a IA preenche o front-matter (todo autor)

**Princípio único:** ninguém preenche metadado à mão. A IA gera o Documento +
front-matter já no vocabulário controlado, seguindo uma **guideline**; o ingest é
**determinístico** (ver [ADR-0005](adr/0005-autoria-nao-tecnico-guideline-paste.md)).
Dois fluxos, mesmo princípio:

```
TI (no repo):
  DEV ─► grill-me-with-docs ─► doc + front-matter ─► commit ─► módulo empurra

Não-técnico (no Brainiac):
  PESSOA ─copia─► GUIDELINE ─cola no► CLAUDE WEB ─gera─► doc + ---front-matter---
  PESSOA ─cola no► BRAINIAC ─parse determinístico + valida vocab─► confirma ─► salva
  (zero IA no Brainiac · nunca vê git/markdown/form · dropdowns pré-preenchidos)
  v2: chat conversacional embutido no Brainiac (mesmo backend determinístico)
```

---

## 8. Coleção — onboarding / handbook

Onboarding **não é um tipo** — é uma **Coleção**: trilha **ordenada** que aponta
pra Entradas existentes (cross-andar) + faceta própria (publico_alvo). A
pertinência mora na Coleção, não na Entrada.

```
COLEÇÃO "Onboarding Dev"  (publico_alvo: TI)
  1. explanation: visão de negócio   (Brainiac)
  2. explanation: arquitetura         (repo)
  3. reference: módulo pagamentos     (repo)
  4. how-to: subir o ambiente         (repo)
```

---

## 9. Reconciliação com a sessão 1

| Sessão 1 | Agora |
|---|---|
| Propósito (referencia/how-to/explicacao/decisao/processo) | **Tipo** (dicionário compartilhado, + spec/plan/README/CONTEXT, classe evergreen/datado) |
| Facetas (departamento, publico_alvo, projeto) | **Metadado core** |
| Catálogo | A **visão federada** (Brainiac indexando + repos) |
| Entrada | unidade do catálogo (Brainiac-native ou federada do repo) |
| Coleção (trilha ordenada) | **mantida** (view no Brainiac, cross-andar) |
| status / resumo / palavras_chave | **mantidos** (core); `nivel_tecnico` e `revisao_ate` **removidos** |
| relacionamentos (substitui/relacionadas/depende_de/parte_de) | casam com o `related` do front-matter he4rt |
| `id` universal `DOC-NNNN` | **RESOLVIDO** (ADR-0006) — id por origem qualificado pela sigla do Projeto: `RPQ:adr/0001`, `Brainiac` usa `PRD-NN` |
| (novo) Projeto como faceta | **Projeto é entidade de 1ª classe** (sigla canônica), não só faceta |
| Catálogo de refs de design | fora de escopo (projeto do Design) |

---

## 10. Pontos abertos

- **PRD vs Regra: RESOLVIDO** (ADR-0007) — o tipo central de produto é o **PRD**
  (feature/grupo, versionado); regras de negócio são seção interna; a visão macro
  é uma **Visão de produto** (explanation) por Projeto.
- **Federação na prática: RESOLVIDO** (ADR-0009) — **push pelo módulo**: o comando
  `docs:publish` empurra um snapshot completo pro webhook do Brainiac (manual, do
  `main`, com guarda; token + HMAC). Sem token do GitHub, sem CI, sem registro de
  rotas, sem poll. Detalhe em [federacao.md](federacao.md).
- **Formato e render: RESOLVIDO** (ADR-0010) — **markdown** é o formato canônico
  (nativo + espelho) e o Brainiac é o **único renderizador** (on-read, com cache);
  o publicador manda markdown, não HTML. Metadado não descreve o corpo (fatos do
  corpo são derivados no ingest).
- **Ciclo de vida do PRD: RESOLVIDO** (ADR-0011) — *salvar ≠ publicar*; a versão
  **congela ao publicar**; só o texto versiona; menor/maior é declarado pelo Produto.
- **Governança do PRD (ADR-0008):** hoje é **sinal social** por `status` (sem gate
  rígido). Revisitar quem pode publicar quando o time crescer, já que publicar o PRD
  dispara spec + código.
- **Marketing e Negócio:** que tipos/extensões de metadado precisam.
- **v2 chat:** quando e como embutir.
- **Artefato: RESOLVIDO** (ADR-0012) — asset HTML por **link**, embutido em **iframe
  de origem isolada**; em doc com corpo o link mora no corpo (derivado), o campo
  explícito só pra Entrada só-artefato; hospedagem externa (waifuvault) por enquanto.
- **Stack do Brainiac:** Laravel + Filament (autoria do PRD + edição do subconjunto
  editável de metadado) + Livewire (vitrine de leitura) + `commonmark`/`highlight`
  (render). **Recuperação por IA = API com filtros** sobre o vocabulário controlado
  (sem embeddings/semântica por agora — reversível, dá pra plugar `pgvector` depois).
  **Busca humana adiada**: quando precisar, o barato é Postgres FTS + filtros por
  faceta (motor dedicado só se a escala pedir). *Auth (um usuário por enquanto) e
  infra/hosting adiados.*
