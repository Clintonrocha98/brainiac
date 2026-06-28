# Pesquisa adversarial — modelagem de documentação vs. Brainiac

> Confronto do desenho do Brainiac (poc-doc) com o estado da arte de modelagem de documentação — open-source e empresas — para encontrar blind spots e enriquecer o desenho. Catorze tradições foram usadas como lentes de ataque, em duas rodadas. Este relatório consolida o que sobreviveu ao confronto.

> **Proveniência:** gerado por pesquisa adversarial automatizada em 2026-06-27 (14 tradições/lentes, 2 rodadas). É **insumo de pesquisa, não decisão canônica** — as recomendações precisam ser triadas e, quando aceitas, viram ADR. Índice e contexto em [README.md](README.md).

---

## Sumário executivo

O Brainiac acertou a **infraestrutura e a higiene**: markdown canônico com render único e HTML como cache descartável (ADR-0010), ingest determinístico com metadado derivado do corpo (ADR-0005), vocabulário controlado de baixa cardinalidade (ADR-0001), id estável desacoplado de slug (ADR-0006), snapshot idempotente com deleção que propaga (ADR-0009), freeze-on-publish do PRD (ADR-0011) e iframe sandbox sem `allow-same-origin` (ADR-0012). Essas decisões-âncora são compartilhadas por Diátaxis, DITA, AI/KO, schema.org, Docs-as-Code, Backstage, GitLab e OWASP. **Não devem ser revertidas.**

O fio condutor de **todas** as frentes adversariais é o mesmo: o Brainiac construiu o ponto de controle certo, mas **não escreveu a invariante em cima dele**. Tem render único — mas não escreveu o gate de sanitização nem o de acessibilidade. Tem ingest determinístico — mas não deriva alt/accDescr nem schema_version. Tem estado `obsoleto` — mas nenhuma ação de saída acoplada a ele. Tem snapshot idempotente — mas nenhum circuit-breaker de deleção. Tem vocabulário fechado — mas nenhuma regra de retenção. A alavanca existe; a regra não.

A descoberta mais grave da rodada 2 é que **a disposição é a metade do ciclo de vida que o desenho inteiro ignorou** (Records/ISO 15489). O `obsoleto` é um estado terminal morto — pinta um badge e nada mais. Versões antigas de PRD e docs obsoletos ficam para sempre competindo na recuperação que alimenta a IA. Essa patologia é **distinta de staleness**: não é o doc estar desatualizado, é o **volume de ruído ativo** competindo com o sinal — e cresce no tempo mesmo se cada doc estiver fresco. Num corpus que alimenta IA, a versão revogada vira contradição confiante, não falha óbvia.

A segunda descoberta dominante é que **segurança foi tratada como checkbox, não como modelagem de ameaça** (STRIDE/Shostack/OWASP). Nenhum ADR escreve a pergunta 2 ("o que pode dar errado") nem a 4 ("como validamos"). A costura mais perigosa — `repo de cliente (módulo) → webhook único → espelho` — nunca é desenhada como fronteira de confiança. "Deleção propaga" é vetor destrutivo default; "sanitização" é palavra solta sobre markdown semi-confiável (stored-XSS); o módulo onipresente é supply-chain única sem SBOM. A escolha PUSH e o iframe sandbox estão **certos no princípio** — falta a disciplina de ameaça em volta deles.

A terceira: **acessibilidade é propriedade estrutural ausente** (WCAG/POUR). A Entrada só-artefato — caso dominante para Design/Marketing — pode ser 100% inacessível, sem equivalente textual nem gate que o exija. O schema não tem um único campo de a11y. E é, de novo, o mesmo padrão: o ingest determinístico e o render único são a alavanca de shift-left perfeita e desperdiçada.

A quarta: **evolução de schema não tem modelo** (expand/migrate/contract). Não há `schema_version` no front-matter (ingest binário válido/inválido, sem upcast); a reconciliação sessão1→2 travou no EXPAND; a sigla imutável não tem rota de rename; o `departamento` 1→N já pedido não tem mecânica de cardinalidade segura. O projeto, com ~20 arquivos, **já falhou** em se manter consistente uma vez.

A taxonomia de 5 "propósitos" **não é MECE** (Diátaxis/DITA/AI-KO/writing-for-task convergem): `decisão` é subespécie de `explicação`, `processo` é departamento disfarçado, e falta o modo `tutorial` — justo o que serve o onboarding que o próprio Brainiac modela como Coleção ordenada. A promessa "recuperável por IA" está vazia no v1: é só pré-filtro por faceta — sem FTS sobre o corpo, sem chunk, sem ranking, sem llms.txt.

Por fim, o Brainiac **não come a própria comida**. O resíduo das duas sessões (`taxonomia.md`/`exemplos-entradas.md` ainda em `proposito`/`DOC-NNNN`) é, ao mesmo tempo, violação de SSOT, expand-sem-contract de migração, e obsoleto-marcado-mas-não-disposto de records — prova viva, em três lentes, de que disciplina humana pura (ADR-0008) já falhou aqui e voltará a falhar sem lint/runner/CI.

---

## Blind spots priorizados

Cada item: **o que é · por que importa · severidade · fontes · recomendação**.

### 1. Disposição inexistente — `obsoleto` é estado terminal morto
- **O que é:** O ciclo de vida termina em `obsoleto` (um badge). Nada sai do índice de IA, nada sai do ranking humano, a pilha de versões de PRD cresce monotônica. A palavra "arquivar" não existe no desenho (verificado: zero ocorrências de retenção/disposição/arquivar em `docs/`). O ADR-0010 ainda **descartou explicitamente** `revisao_ate`.
- **Por que importa:** Records (ISO 15489/Schellenberg/DoD 5015.2/macro-appraisal) abre uma patologia distinta de staleness: o problema é o **volume de ruído ativo** competindo com sinal — versões antigas de PRD são contradições diretas da verdade corrente, ruído de altíssima toxicidade para IA. Você construiu metade da máquina de estados (o estado final), falta a transição de saída. O obsoleto **órfão** (sem sucessor) nunca é apanhado por status+supersede.
- **Severidade:** Alta.
- **Fontes:** Records (ISO 15489, Schellenberg, DoD 5015.2, MoReq2010, Terry Cook), AI-native/RAG.
- **Recomendação:** Separar os dois eixos que o ADR-0008 fundiu: manter governança SOCIAL de autoria, mas acoplar uma AÇÃO determinística ao estado terminal. Mínimo: `status=obsoleto` ⇒ (a) excluído do índice de IA por default; (b) recolhido atrás de "mostrar arquivados" na vitrine humana. Idem pilha do PRD: recuperação ativa serve só a última versão publicada; v1.0/v1.1 viram record permanente, auditável por `RPQ:PRD-12@v1.0` mas FORA do índice default. Novo ADR "Disposição: o que `obsoleto` FAZ".

### 2. Deleção propaga sem soft-delete nem circuit-breaker
- **O que é:** "Snapshot completo, deleção propaga" (ADR-0009) é a operação mais destrutiva do sistema e é a default. Um token vazado ou um `main` com `docs/` apagado zera o espelho em um POST. Sem soft-delete, sem confirmação, sem limiar, sem versão recuperável, sem hold para records.
- **Por que importa:** AppSec (STRIDE) reenquadra a "idempotência" como a pior vulnerabilidade de DISPONIBILIDADE: Spoofing (token vazado) habilita DoS/destruição total. Records confirma o espelho oposto: poda nenhuma para o lixo E proteção nenhuma para o tesouro (PRD congelado/ADR/spec de valor de contrato saem sem rastro). O ADR-0009 não nomeia "deleção propaga" como risco em lugar nenhum (verificado).
- **Severidade:** Alta.
- **Fontes:** STRIDE/DoS, OWASP Threat Modeling, Records (legal hold, DoD 5015.2).
- **Recomendação:** (1) soft-delete — docs ausentes do snapshot viram "arquivadas/órfãs", não apagadas; (2) circuit-breaker — publish que remove acima de um limiar (>30% ou tudo) exige confirmação fora-de-banda; (3) versionar o snapshot recebido para rollback; (4) entradas de classe `record` protegidas (disposition/legal hold): deleção via snapshot marca "removida na origem" e preserva por um período com log.

### 3. Threat model ausente — nenhum ADR escreve a pergunta 2
- **O que é:** Zero seção de ameaças, zero DFD, zero tabela STRIDE, zero assunções datadas/checáveis. O webhook único combate só Information Disclosure (evitar token org-wide) e deixa 5 categorias STRIDE descobertas. Sem frescor/anti-replay, sem nonce, sem binding token↔sigla, sem comparação em tempo constante, sem trilha de auditoria, sem rate limiting. Nenhuma mitigação afirmada tem teste pareado (pergunta 4).
- **Por que importa:** Shostack/OWASP/Threat Modeling Manifesto: "evitar token org-wide" é segurança-como-checkbox. Sem binding token↔sigla, o token de um projeto sobrescreve o espelho de outro (EoP). Sem trilha, não há forense nem rollback-por-autor (Repudiation).
- **Severidade:** Alta.
- **Fontes:** STRIDE, OWASP Threat Modeling Cheat Sheet, Shostack 4-Question Frame, SLSA/SBOM.
- **Recomendação:** Tabela STRIDE-por-elemento do `/webhook/ingest`, uma linha por categoria. Concretamente: assinar timestamp + janela curta (anti-replay); nonce/request-id com TTL; binding token↔sigla no servidor; `hash_equals`; persistir (sigla, commit SHA, autor, timestamp) por publish; rate limiting + limite de payload; rotação/revogação/expiração de secret por projeto.

### 4. Stored-XSS via markdown — defesa é só a palavra "sanitização"
- **O que é:** O Brainiac é o ÚNICO renderizador de markdown semi-confiável de N repos. CommonMark por padrão PASSA HTML inline bruto e URLs `javascript:`/`data:`. `[x](javascript:...)`, `<img onerror=...>` ou HTML cru viram stored-XSS na sessão de qualquer leitor humano. O ADR-0010 trata markdown só como "fonte limpa para busca", nunca como superfície de Tampering. Verificado: sem nomear HTMLPurifier, sem `allow_unsafe_links=false`, sem `securityLevel` do mermaid, sem suíte de regressão XSS.
- **Por que importa:** O autor não-técnico cola conteúdo (ADR-0005) e o TI publica markdown de repo — o corpo é entrada semi-confiável. Tampering → Information Disclosure (roubo de sessão de leitores).
- **Severidade:** Alta.
- **Fontes:** OWASP XSS/HTML5, doc do `league/commonmark`, Shostack pergunta 4.
- **Recomendação:** No ADR-0010: nomear o sanitizador (HTMLPurifier sobre o HTML de saída), desabilitar/allowlist de HTML inline, bloquear `javascript:`/`data:` (`allow_unsafe_links=false`), declarar `securityLevel` restrito do mermaid, e seção "Como validamos" com suíte de payloads XSS de regressão renderizada e verificada.

### 5. Acessibilidade ausente como propriedade estrutural
- **O que é:** Schema sem nenhum campo de a11y (verificado: zero alt/aria/lang/title/accDescr em `docs/`). Artefato só-JS sem equivalente textual; mermaid sem accDescr; iframe sem `title` (ADR-0012 nunca menciona — violação H64).
- **Por que importa:** WCAG 1.1.1 (nível A, o piso): todo não-texto exige equivalente de propósito. A Entrada só-artefato (caso dominante para Design/Marketing) pode ser 100% inacessível a leitor de tela/teclado. A11y fica 100% à mercê da disciplina do autor — quando o ingest determinístico (ADR-0005) e o render único (ADR-0010) são a alavanca de shift-left perfeita e desperdiçada. Para público "externo" há exposição legal (EAA/EN 301 549 desde 28/06/2025).
- **Severidade:** Alta.
- **Fontes:** WCAG 1.1.1/2.4.1/3.1.1, OWASP HTML5, mermaid docs, EAA/EN 301 549, Plain Language.
- **Recomendação:** Entrada só-artefato exige `descricao_acessivel` obrigatória + `title` no iframe; bloco mermaid sem accTitle/accDescr = erro/warning no mesmo parser que já deriva "tem mermaid"; adicionar `idioma` ao core (default pt-BR → `lang=`); render centralizado garante saída semântica (um `<h1>`, hierarquia sem saltos, landmarks); axe-core/pa11y no CI. Novo ADR "Acessibilidade como invariante de schema e de render".

### 6. Taxonomia de 5 propósitos não é MECE
- **O que é:** A lista mistura três eixos: modo de necessidade (referência/how-to/explicação) + formato (`decisão`/ADR) + departamento (`processo`). `decisão` é subespécie de `explicação`; `processo` é "fluxo de uma área" = departamento reintroduzido no topo. Falta o modo `tutorial`. O ATRITO 1 do próprio repo ("um README = 3 Entradas") desmente a exclusividade mútua reivindicada (CONTEXT.md L134).
- **Por que importa:** Quatro frentes convergem (Diátaxis, DITA, AI/KO, writing-for-task). Sem o eixo aprender×trabalhar, o onboarding (Coleção ordenada = forma de tutorial) fica sem o modo que o serve. Classificação inconsistente na origem degrada silenciosamente a recuperação facetada — e não há bússola para o autor decidir o propósito de um trecho ambíguo.
- **Severidade:** Alta.
- **Fontes:** Diátaxis (compass, tutorials-how-to, explanation), DITA (Concept/Task/Reference), AI/KO, writing-for-task.
- **Recomendação:** Separar MODO (célula Diátaxis, eixo de classificação) de FORMATO (ADR/spec/plan/PRD/README, faceta). Colapsar `decisão` em `explicação`; eliminar `processo` (executável → how-to; justificativa/handoff → explicação; agrupamento → Coleção filtrada por departamento); adicionar `tutorial` OU declarar explicitamente no ADR-0001 que a fusão é escolha pragmática (com a página tutorials-how-to citada). Escrever a tabela canônica FORMATO→MODO.

### 7. Anti-rot inexistente — frescor terceirizado para disciplina humana
- **O que é:** Gatilho de publish manual, badge passivo de staleness sem dono, `revisao_ate` descartado, `status` como sinal social sem registro de quem/quando promoveu. `atualizado_em` é toque, não verdade conferida.
- **Por que importa:** Convergência de 5 frentes (Docs-as-Code, doc rot, GitLab, Backstage, RFC) + macro-appraisal. "status: publicado" de 2 anos é indistinguível de verdadeiro; o obsoleto órfão apodrece como verdade aparente porque status+supersede dependem de ação humana voluntária — é o esquecimento, não a má-fé, que enche o corpus. `owner` é e-mail de pessoa: bomba-relógio quando ela sai. Doc podre vira erro confiante propagado para Spec/ADR/código.
- **Severidade:** Alta.
- **Fontes:** Doc rot/freshness (SWE at Google cap. 10, Tom Johnson), Docs-as-Code, GitLab, Backstage, RFC, macro-appraisal (Cook).
- **Recomendação:** Coluna `revisado_em` (humano, manual) DISTINTA de `atualizado_em` (git/automático); byline "Última revisão por {owner} em DD/MM". TTL de revisão POR TIPO (decisão/ADR longo; processo semestral; referência/how-to por release) e varredura que notifica o owner quando estoura — **gatilho, não gate**. Expor o sinal de frescor na rota de IA. `owner` deve ser referência a Time/Pessoa, não e-mail solto.

### 8. "Recuperável por IA" é só pré-filtro por faceta
- **O que é:** Filtro sobre 4 facetas de cardinalidade baixíssima restringe escopo, mas nunca acha o trecho que responde. Não existe "chunk" — só Entrada inteira (estoura a janela). Busca textual ADIADA, embeddings descartados. Não há ranking, llms.txt, busca híbrida.
- **Por que importa:** AI-native/RAG, Backstage e GitLab convergem: metadata filtering é PRÉ-filtro combinado COM a query, não substituto. A base é densa em handles exatos (siglas, ids, códigos de erro) onde embedding puro falha — o destino tem de ser HÍBRIDO. Acoplamento de rodada 2: FTS sem regra de disposição só faz o ruído acumulado competir melhor com o sinal.
- **Severidade:** Alta.
- **Fontes:** AI-native/RAG (Anthropic Contextual Retrieval, AWS RAG, llms.txt), Backstage, GitLab.
- **Recomendação:** Ligar Postgres FTS (`tsvector`+`ts_rank` sobre titulo+resumo+corpo+palavras_chave) JÁ no v1, na mesma rota para humano e IA. Roadmap explícito: FTS lexical (já) → +pgvector (depois) → +reranking, destino HÍBRIDO declarado. Recuperação default filtra `status=obsoleto` e versões-não-correntes de PRD por default.

### 9. id canônico amarrado à sigla mutável
- **O que é:** ADR-0006 promete id "estável, nunca muda", mas o id é qualificado pela SIGLA (`RPQ:adr/0001`), o metadado mais sujeito a rename/merge/reciclagem. `RPQ→RPX` quebra de uma vez todos os ids, relacionamentos, links de spec (`RPQ:PRD-12@v2.0`) e a chave de junção com o Monday.
- **Por que importa:** Grafos/PIDs (ARK, "Cool URIs don't change") e AI/KO: confunde AUTORIDADE (não querer ser dono) com PERSISTÊNCIA (sobreviver a rename). A rodada 2 (migração) agrava: não há `siglas_anteriores`, não há resolução com alias, não há instrumentação de quem pina a sigla velha — "aposentar a sigla" (já citado como caso) seria um big-bang sem janela de deprecation.
- **Severidade:** Alta.
- **Fontes:** Grafos/PIDs (Berners-Lee, ARK Alliance), AI/KO, Migração (Ambler/Sadalage, Hodgson/CDC).
- **Recomendação:** Separar identidade opaca imutável (ULID/NOID por Entrada) do handle nativo (`adr/0001`, atributo) e da sigla (rótulo cosmético resolvível). Manter `RPQ:adr/0001` como alias legível, mas relacionamentos e chave de junção apontam para o id opaco. Pragmatismo de v1: adicionar `aliases[]` e `siglas_anteriores[]` + `sigla_canonica_atual` já agora, para rename ser aditivo.

### 10. Schema sem versão e sem processo de migração
- **O que é:** Sem `schema_version` no front-matter (verificado) — o ingest é binário (casa com hoje / inválido), sem upcast. A reconciliação sessão1→2 é EXPAND (forma nova proposta) sem MIGRATE (backfill dos exemplos) sem CONTRACT (remoção da velha). O `departamento` 1→N já pedido não tem mecânica de cardinalidade segura.
- **Por que importa:** Migração (Fowler Parallel Change/Hodgson/Ambler): um módulo desatualizado é rejeitado em bloco OU sobrescreve o espelho com a forma antiga via "deleção propaga". O projeto, com ~20 arquivos, já não se manteve consistente; multiplique por milhares de Entradas federadas. Não há catálogo de refatorações, runner, rollback, nem instrumentação de stragglers.
- **Severidade:** Alta.
- **Fontes:** Migração de schema (Fowler, Hodgson, Ambler/Sadalage, gh-ost/pgroll, Wellhausen).
- **Recomendação:** `schema_version` no front-matter e no envelope do snapshot; ingest faz upcast determinístico (cadeia v1→v2→v3 em memória, depois valida). ADR-0013 "Evolução de schema" adotando expand/migrate/contract; `docs/migrations/` com migrações numeradas; tratar a reconciliação sessão1→2 como migration 0001 com runner idempotente + dry-run. Separar "mudança de schema" (rápida) de "backfill" (job de fundo, lotes, retomável).

### 11. Gatilho de publish manual + ausência de CI de validação
- **O que é:** Publish disparado por humano + badge passivo. Sem CI, não há onde rodar link-check, validação de front-matter contra o vocabulário, lint de prosa, detecção de markdown que não renderiza, suíte XSS, linter de a11y, validação de `schema_version`.
- **Por que importa:** Docs-as-Code/GitLab/Backstage convergem: "publicado=merjado" só se o gatilho for o merge; badge passivo é freshness audit (abordagem reprovada) sem dono. Separar "CI como gatilho" (evitável) de "CI como porteiro de qualidade" (coração do Docs-as-Code).
- **Severidade:** Alta.
- **Fontes:** Docs-as-Code (Write the Docs, Netlify, Cloudflare), GitLab, Backstage, AppSec, WCAG.
- **Recomendação:** Manter PUSH, mas disparar `docs:publish` por git hook `post-merge` no `main` OU GitHub Action mínimo escopado a UM repo (POST+HMAC), para "publicado"="merjado". CI por repo como porteiro de qualidade (sem token org-wide, sem alcançar o Brainiac): link-check, validação de front-matter+schema_version, lint de prosa, regressão XSS/a11y. Badge acionável (alerta ao owner). Exibir delta (criadas/atualizadas/DELETADAS) com confirmação acima do limiar.

### 12. O Brainiac não come a própria comida
- **O que é:** 12 ADRs sem Status/date; template inconsistente (0001-0008 sem headers vs 0009-0012 com); ADR-0002 com nome de arquivo que mente (`0002-topologia-hibrida-pull.md` enquanto a federação real é PUSH); errata-por-blockquote no corpo; `taxonomia.md`/`exemplos-entradas.md` ainda em `proposito`/`DOC-NNNN`/`descricao` (o id PROIBIDO). Wikilinks `[[arquivo]]` que não renderizam no GitHub.
- **Por que importa:** ADR, writing-for-task, GitLab e records convergem (verificado no repo): é a prova viva — em TRÊS lentes (SSOT, migração expand-sem-contract, records obsoleto-não-disposto) — de que disciplina humana pura (ADR-0008) já falhou uma vez aqui. Sem lint/runner, falha de novo em escala.
- **Severidade:** Alta.
- **Fontes:** Tradição ADR (Nygard/MADR), writing-for-task, GitLab/SSOT, Migração, Records.
- **Recomendação:** Frontmatter MADR-mínimo (status, date, deciders, supersedes/superseded_by) em todos os ADRs; marcar 0002 substituído por 0009; parar de editar corpo de ADR aceito; UM template (Contexto/Decisão/Opções/Consequências) retrofitado; reconciliar `taxonomia.md`/`exemplos-entradas.md` (migration 0001); trocar `[[wikilink]]` por links markdown relativos; lint de vocabulário no CI.

### 13. Schema sem metadados de retenção
- **O que é:** Tudo é tratado com igual peso de valor — sem distinguir `record` (contrato/auditoria: PRD congelado, ADR, spec) de `transitório` (rascunho/nota). Sem gatilho de revisão, regra de retenção, destino de disposição, classe de valor. A distinção evergreen/datado existe (CONTEXT.md) mas é sobre EDIÇÃO, não sobre VALOR DE RETENÇÃO — eixos ortogonais confundidos.
- **Por que importa:** Records (ISO 15489/Schellenberg): sem classe de retenção, o corpus não tem como saber o que deveria sair, quando, nem por quê — a patologia do corpus monotônico. Big-bucket retention (NARA) prova que o mínimo viável não é zero regras: 2-3 buckets fecham o ciclo.
- **Severidade:** Média.
- **Fontes:** Records (ISO 15489, Schellenberg, GARP/ARMA, NARA macro-appraisal).
- **Recomendação:** `classe_de_retencao` (vocab curto: `record` / `transitório`) + `gatilho_revisao` por tipo (inativo há N meses / superseded / nunca). Acopla com o hold do ataque 2: `record` = protegido.

### 14. ADR-0012: iframe sandbox correto no princípio, afirmado sem invariante
- **O que é:** A decisão de origem separada sem `allow-same-origin` é DEFENSÁVEL e correta, mas (a) o ADR não fixa o conjunto de flags como invariante testável; (b) waifuvault é multi-tenant — "origem isolada" vale por host, não por dono (um segundo artefato malicioso no mesmo host é same-origin e lê o storage do outro); (c) nenhuma CSP/frame-ancestors citada; nada impede fetch outbound do artefato (exfiltração). Falta a pergunta 4 (teste que prove o isolamento).
- **Por que importa:** OWASP HTML5: `allow-scripts` + `allow-same-origin` juntos anulam o sandbox. Multi-tenancy não é fronteira de confiança por dono.
- **Severidade:** Média.
- **Fontes:** OWASP HTML5 Security Cheat Sheet, MDN sandbox, SLSA (host compartilhado).
- **Recomendação:** Fixar flags como invariante ("`sandbox=allow-scripts`; JAMAIS `allow-same-origin`"); reconhecer que waifuvault não isola artefatos entre si (risco aceito OU promover store próprio com origem por artefato); declarar CSP (`frame-ancestors 'self'`); seção "Como validamos".

### 15. Sem transclusão/single-source para conteúdo
- **O que é:** Regra de negócio cross-feature é SEÇÃO dentro de cada PRD (ADR-0007) → copiada em N PRDs. Coleção remonta links, não conteúdo. Sem include/keyref, mudar uma regra exige editar N corpos congelados, multiplicado por versão (ADR-0011).
- **Por que importa:** DITA: o teste "ao mudar X, edito em quantos arquivos?" dá a resposta errada (N). O projeto JÁ pratica single-sourcing no eixo PRD→spec, mas o abandona no eixo regra→N-PRDs. A interação congelamento×reúso não foi pensada (contrato congelado puxando conteúdo vivo deixa de ser congelado).
- **Severidade:** Média.
- **Fontes:** DITA/single-source (conref/keyref, warehouse topic), grafos (keyref).
- **Recomendação:** Promover regra cross-feature a Entrada de 1ª classe; transclusão por chave no render-on-read (`{{ ref: RPQ:RN-07 }}`), invalidando cache dos destinos quando a fonte muda. Em PRD congelado, PINAR a versão da fonte (`@v3`) materializando o fragmento.

### 16. Falso binário rascunho/congelado no PRD
- **O que é:** Sem errata (typo → versão menor força ruído na pilha e dispara sinal de contrato ao TI sem mudança real), sem estado provisório, sem janela de objeção na major. A major é classificada e congelada por uma pessoa só, sem segundo par de olhos (combinado com ADR-0008 sem RBAC).
- **Por que importa:** RFC/PEP/spec-as-contract: a IETF resolveu isso com três mecanismos distintos (errata/Updates/Obsoletes) e estado intermediário (Proposed/Provisional) + janela FCP. Forçar typo→versão treina o TI a ignorar bumps (boy-que-gritou-lobo).
- **Severidade:** Média.
- **Fontes:** RFC/PEP/spec-as-contract (IETF errata, Rust RFC FCP, oasdiff).
- **Recomendação:** Errata anexada à versão congelada (não incrementa versão nem dispara sinal); se muda intenção, é versão nova (regra de ouro IETF). Diff textual da última versão publicada no publish; janela de objeção curta antes do PRD virar base de spec (FCP leve, sem RBAC).

### 17. Nível de abstração errado — documento, não ecossistema de software
- **O que é:** `module` é string, `owner` é texto/departamento, não há entidade Time/Pessoa/API/Sistema nem papel de records manager. Relações são mantidas à mão (grafo parcial, incoerente) em vez de derivadas.
- **Por que importa:** Backstage/grafos/records: não dá para perguntar "o que quebra se pagamentos mudar?" nem "Entradas que o time X possui". Falta camada de maturidade (scorecards) e contribuição de baixo atrito. Quem ARQUIVA/remove o obsoleto não tem resposta no desenho.
- **Severidade:** Média.
- **Fontes:** Backstage, grafos de conhecimento, AI/KO, GitLab, Records.
- **Recomendação:** Promover `module` a entidade Módulo sob o Projeto com `dependsOn`; `owner` como referência a Time/Pessoa (tabela seedada), distinta de `departamento`, validada; papel de DONO DA DISPOSIÇÃO; relações derivadas no ingest (declara um lado, materializa o inverso); endpoint de TRAVESSIA (vizinhança tipada a N hops).

### 18. Faceta `departamento` travada em cardinalidade 1
- **O que é:** Funde "dono editorial" com "área-de-assunto" (dois conceitos ortogonais) e não representa conteúdo cross-área. Visão de produto (Produto+Negócio), handoff (cruza times) e arquitetura (TI+Produto) não cabem em `departamento=1` (verificado: "enum Área (1)"). Recriou o silo dentro da faceta.
- **Por que importa:** AI/KO (Ranganathan, SKOS polihierarquia): um item recebe múltiplos valores numa faceta (pós-coordenação). `publico_alvo` e `projeto` já são multi-valor — a assimetria é arbitrária. Falta governança do vocabulário (quem aprova/aposenta) e validação empírica (card sorting) — a taxonomia veio do organograma.
- **Severidade:** Média.
- **Fontes:** AI/KO (Z39.19, SKOS, card sorting), Migração.
- **Recomendação:** Separar `owner_area` (cardinalidade 1, responsabilidade editorial) de `areas_de_assunto` (multi-valor). Executar como Parallel Change. Adicionar processo de governança do vocabulário (ADR separado do ADR-0008). Validar a distinção com card sort antes de cravar.

### 19. Acessibilidade cognitiva (linguagem simples) ausente
- **O que é:** Os próprios ADRs são densos, com jargão ("idempotente", "HMAC", "render-on-read memoizado") e parágrafos longos. O campo `resumo` é o único gesto, mas serve a preview/IA, não a uma política de redação. Vocabulário inconsistente (resíduo das 2 sessões) é barreira de compreensão real.
- **Por que importa:** WCAG 1.1.1 + Plain Language: o público inclui não-técnicos, liderança e "externo". Exclui baixa literacia, deficiência cognitiva e não-nativos.
- **Severidade:** Baixa.
- **Fontes:** WCAG 1.1.1, Federal Plain Language Guidelines.
- **Recomendação:** Plain language como princípio de autoria (voz ativa, frase curta, glossário obrigatório quando público inclui não-TI/externo); reaproveitar `resumo` como resumo de leitura fácil; reconciliar o resíduo; CONTEXT.md como camada de "defina o jargão" no render.

### 20. Sem disposition/legal hold nem trilha de auditoria de disposição
- **O que é:** A única forma de disposição é deletar o arquivo no repo, e o snapshot propaga. Qualquer apagão de `docs/*.md` num main limpo + `docs:publish` DESTRÓI silenciosamente PRDs congelados/ADRs/specs, sem trava, registro ou recuperação.
- **Por que importa:** DoD 5015.2/MoReq2010 + AppSec (Repudiation): ISO 15489 exige integridade/confiabilidade — deleção silenciosa e irrastreável de um PRD congelado viola ambas.
- **Severidade:** Baixa.
- **Fontes:** DoD 5015.2, MoReq2010, ISO 15489, STRIDE (Repudiation).
- **Recomendação:** Classe `record` protegida + log de ingest (quem dispôs do quê, quando), sem RBAC nem workflow pesado.

---

## Enriquecimentos propostos

| Proposta | O que muda | Esforço | Fontes |
|---|---|---|---|
| **Busca textual (Postgres FTS sobre o CORPO) no v1** | `tsvector`+`ts_rank` sobre titulo+resumo+corpo+palavras_chave, mesma rota humano/IA. Roadmap declarado FTS→+pgvector→+reranking, destino híbrido. Filtra `obsoleto` e versões-não-correntes por default. | baixo | AI-native/RAG, Backstage, GitLab, Diátaxis, Records |
| **Disposição como REGRA DE MÁQUINA** | Separar governança social de autoria da ação determinística no estado terminal: `obsoleto` ⇒ fora do índice de IA por default + recolhido na vitrine. Pilha de PRD: só a última publicada na recuperação ativa. Conceito "arquivado" ≠ "deletado do git". ADR novo "Disposição". | médio | Records (ISO 15489, Schellenberg, DoD 5015.2, continuum), AI-native/RAG |
| **Metadados de retenção no schema** | `classe_de_retencao` (`record`/`transitório`) + `gatilho_revisao` por tipo (reabrir `revisao_ate` como gatilho de appraisal, não data de validade ingênua). Pega o obsoleto órfão. | médio | Records (ISO 15489, Schellenberg, Cook macro-appraisal, GARP/ARMA, NARA) |
| **Acessibilidade como invariante de schema e render** | `descricao_acessivel` obrigatória em só-artefato + `title` no iframe; mermaid sem accDescr = warning no ingest; `idioma` no core → `lang=`; render semântico (um `<h1>`, landmarks, `allow_unsafe_links=false`); axe-core/pa11y no CI. ADR novo. | médio | WCAG 1.1.1/1.3.1/2.4.1/3.1.1, OWASP HTML5/XSS, Plain Language, mermaid |
| **Threat model do webhook (STRIDE-por-elemento)** | Tabela STRIDE no ADR-0009 + defense-in-depth: timestamp assinado/anti-replay, nonce/TTL, binding token↔sigla, `hash_equals`, trilha de auditoria, rate limiting, rotação de secret. | médio | STRIDE, OWASP Threat Modeling, Shostack, webhook (Stripe) |
| **Deleção não-destrutiva** | Soft-delete + circuit-breaker de diff (>30% ou tudo = confirmação) + snapshot versionado + hold para `record`. Terceiro destino do snapshot além de presente/ausente. | médio | STRIDE/DoS, OWASP, Records (DoD 5015.2, MoReq2010, legal hold) |
| **`schema_version` + upcast determinístico** | Versão no front-matter e no envelope; ingest faz upcast (cadeia de migrações em memória) antes de validar. Módulo carimba a versão que conhece, straggler fica visível. | médio | Migração (Fowler, pgroll, neo4j-migrations) |
| **ADR-0013 "Evolução de schema"** | expand/migrate/contract obrigatório; `docs/migrations/` numeradas; reconciliação sessão1→2 como migration 0001 com runner idempotente + dry-run; separar "mudança de schema" de "backfill". | alto | Migração (Fowler, Hodgson, Ambler/Sadalage, gh-ost/pt-osc, Wellhausen) |
| **`departamento` 1→N como Parallel Change** | Decidir co-donos vs "também relevante" (talvez já seja `publico_alvo`); se co-donos: EXPAND (`departamentos[]` ao lado de `departamento` owner) → BACKFILL → MIGRATE leitores → CONTRACT. | alto | Migração (Ambler/Sadalage Change Cardinality, APOC), AI/KO |
| **Conceito × rótulo + instrumentação de stragglers** | Cada valor de faceta = conceito com id + prefLabel/altLabel/hiddenLabel; referenciar por id. Sigla: `siglas_anteriores[]` + `sigla_canonica_atual`. CDC: spec DECLARA "dependo de `RPQ:PRD-12@v2.0`" e `docs:publish` do consumidor FALHA se o alvo sumiu. | alto | AI/KO (Z39.19, SKOS), Migração (Hodgson/CDC), grafos/PIDs |
| **Frescor como SINAL (não gate)** | `revisado_em` (manual) ≠ `atualizado_em` (git); byline; TTL de revisão por tipo + varredura que notifica o owner; expor frescor na rota de IA. `owner` = referência a Time/Pessoa. | médio | Doc rot/freshness, Backstage (Soundcheck), GitLab/Notion, AI-native, Records |
| **Reacoplar publish ao merge + CI porteiro** | Git hook `post-merge` ou Action escopado a 1 repo para "publicado"="merjado"; CI roda link-check, validação de front-matter+schema_version, lint de prosa, detecção de `[[wikilink]]`, regressão XSS/a11y; exibir delta antes de aplicar. | médio | Docs-as-Code, GitLab, Backstage, AppSec |
| **SBOM + proveniência do módulo de federação** | SBOM do módulo (permissões/escopos), proveniência das releases (assinatura, pinning por hash), linha "raio de alcance se comprometido", processo de rotação de secret. | médio | SLSA, SBOM (Cycode), STRIDE (Tampering/EoP) |
| **Status + supersessão nos próprios ADRs (dogfooding)** | Frontmatter MADR-mínimo em todos os 12; 0002 substituído por 0009, 0003/0004 refinados por 0007; parar de editar corpo aceito; UM template retrofitado; ADRs viram Entradas do Brainiac. `record` protegido. | médio | Tradição ADR (Nygard/MADR), writing-for-task, doc rot, Records |
| **Reconciliar o resíduo das 2 sessões + lint** | Reescrever `taxonomia.md`/`exemplos-entradas.md` para `tipo`/ids qualificados OU marcá-los obsoletos com supersede (dogfood); trocar `[[wikilink]]` por links relativos; lint que falha se `proposito`/`DOC-NNNN`/`descricao`/`nivel_tecnico`/`revisao_ate` reaparecerem. É a migration 0001. | baixo | GitLab SSOT, writing-for-task, ADR, Migração, Records |
| **Errata + diff/janela na major do PRD** | Errata anexada (não versiona, não dispara sinal); diff textual no publish; janela de objeção curta antes de virar base de spec (FCP leve). | médio | RFC/PEP/spec-as-contract |
| **Transclusão por chave no render-on-read** | Regra cross-feature → Entrada de 1ª classe + `{{ ref: RPQ:RN-07 }}` resolvido no render; invalidar cache dos destinos; PRD congelado pina `@v3`. | alto | DITA/single-source, grafos (keyref), RFC (freeze) |
| **Promover `module` a entidade + PRD→Spec→ADR + owner referência** | `module` → entidade Módulo com `dependsOn`; `owner` referência validada; papel de dono da disposição; endpoint de travessia; relações inversas derivadas no ingest. | alto | Backstage, grafos, AI/KO, Records |
| **Coleção que remonta conteúdo + llms.txt + chunking** | Coleção renderiza como UM documento contínuo (não só índice); `/llms.txt` determinístico (respeitando disposição); chunking parent-child por H2/H3 (~200-400 tokens, devolve o pai) com cabeçalho de contexto pré-anexado do metadado. | alto | DITA, AI-native/RAG (llms.txt, Contextual Retrieval), Diátaxis |

---

## Ataques por decisão/ADR

Consolidação dos ataques das catorze frentes, agrupados por decisão. Severidade é a mais alta atribuída entre as frentes.

| ADR/decisão | Crítica | Sev. | Recomendação |
|---|---|---|---|
| **ADR-0001 — 5 propósitos no topo** | Não é MECE: mistura modo + formato + departamento; reivindica exclusividade mútua que o ATRITO 1 desmente; falta `tutorial` (Diátaxis/DITA/AI-KO/writing-for-task) | Alta | Separar MODO de FORMATO; colapsar `decisão`→`explicação`; eliminar `processo`; adicionar `tutorial` ou declarar a fusão como escolha; tabela FORMATO→MODO |
| **ADR-0001 — `departamento` cardinalidade 1** | Funde dono editorial com área-de-assunto; não representa cross-área; recriou silo dentro da faceta; sem governança de vocabulário; taxonomia do organograma sem card sorting | Média | `owner_area` (1) + `areas_de_assunto` (N) via Parallel Change; ADR de governança de vocabulário; validar com usuários |
| **ADR-0001 — vocabulário = id = rótulo** | Sem prefLabel/altLabel/hiddenLabel: renomear quebra tudo, busca só por termo exato (`pagamentos`≠`payments`/`billing`); descartou semântica E synonym ring (pior dos mundos) | Alta | Conceito com id estável + tripla de rótulos; referenciar por id; FTS consulta todos os rótulos |
| **ADR-0002 — nome de arquivo mente** | Arquivo `0002-...pull.md` e corpo dizem PULL; federação real é PUSH (ADR-0009); sem campo Status marcando superseded | Alta (estrutural) | Renomear para nome neutro + status `superseded by 0009`; corrigir rótulo do README |
| **ADR-0003 — PRD vs Spec** | "Mesmo fato em dois idiomas" é duplicação; só funciona enquanto a disciplina mantém Spec derivada do PRD; sem gate | Alta | Spec referencia seção exata do PRD por id+versão, contém só o novo; lint rejeita ref a versão inexistente; aviso de supersede |
| **ADR-0004 — Monday como projeção** | Aplicação textbook de SSOT; menos autoridade externa = janela de migração mais curta | — (defender) | Manter; é boa fronteira |
| **ADR-0005 — ingest determinístico** | Maduro e subaproveitado: é o gancho perfeito para shift-left de a11y e upcast de schema | Baixa | Carregar regras de a11y na guideline; usar o parser para derivar alt/accDescr; sincronizar guideline↔schema_version |
| **ADR-0006 — id qualificado pela sigla** | Semantic rot embutido: rebrand/fusão quebra todo o grafo; sem rota de migração; confunde autoridade com persistência | Alta | id opaco imutável + handle nativo + sigla como rótulo; `aliases[]`/`siglas_anteriores[]` |
| **ADR-0007 — regra dentro do PRD** | Single-source abandonado no eixo regra→N-PRDs; regra cross-feature copiada em N corpos congelados | Média | Promover regra a Entrada; transclusão por keyref com pin de versão |
| **ADR-0008 — governança social** | `status` sem máquina de estados, sem registro de quem/quando promoveu; `obsoleto` sem ação de saída; sem disposição; webhook tem tokens, não usuários | Alta | Registrar transições (quem/quando); máquina de estados; acoplar disposição ao terminal; separar plano humano do plano de tokens; nomear o aceitante do risco |
| **ADR-0009 — federação PUSH** | Direção certa (defender); falta pergunta 2 (STRIDE), deleção propaga sem soft-delete/circuit-breaker, sem anti-replay/binding/auditoria, módulo é supply-chain única | Alta | Tabela STRIDE; soft-delete + circuit-breaker; defense-in-depth de auth; SBOM/proveniência; reacoplar gatilho ao merge + CI |
| **ADR-0010 — markdown canônico + render único** | Base correta (defender); "sanitização" é palavra solta (stored-XSS); descartou `revisao_ate` (anti-rot); ponto único de leitura sem RTO/RPO | Alta | Nomear sanitizador + suíte XSS; reintroduzir frescor como sinal; caminho de leitura degradado |
| **ADR-0011 — freeze-on-publish** | Imutabilidade correta (defender); falso binário rascunho/congelado, sem errata/provisional/janela de objeção; pilha de versões sem disposição na recuperação | Média | Errata como terceiro caminho; estado provisório; diff+janela; recuperação ativa só na última versão |
| **ADR-0012 — iframe sandbox** | Princípio correto (defender); flags não fixadas como invariante; waifuvault multi-tenant não isola artefatos entre si; sem CSP; sem `title` (a11y); sem teste de isolamento | Média | Fixar flags; reconhecer multi-tenancy; declarar CSP; `title` + descrição; "Como validamos" |

---

## Crítica estrutural dos nossos próprios documentos

O que melhorar na FORMA/estrutura do repo (tudo verificado no estado atual):

1. **Resíduo de duas sessões coabitando sem supersessão.** `taxonomia.md` e `exemplos-entradas.md` inteiro (DOC-0001..0003, `proposito`, `descricao`) ainda usam o vocabulário morto da sessão 1, enquanto CONTEXT.md/ADR-0006 já cunharam `tipo` e ids `RPQ:...`. Os ÚNICOS arquivos que mostram o schema em ação usam o id PROIBIDO (`DOC-NNNN` está no _Avoid_). É, ao mesmo tempo: violação de SSOT, expand-sem-contract, e doc obsoleta marcada-mas-não-disposta.
2. **Nenhum dos 12 ADRs tem campo Status nem date** (verificado). O ADR-0002 (PULL) foi revertido para PUSH (ADR-0009) e segue sem marcador de máquina. Leitor/IA lê decisão morta como viva. Ironia tripla: ADR-0008 define status para as Entradas dos outros, ADR-0011 prega imutabilidade do texto, e nada disso é aplicado aos próprios ADRs.
3. **Arquivo e rótulos do ADR-0002 MENTEM sobre a decisão vigente.** O arquivo se chama `0002-topologia-hibrida-pull.md` e fala PULL; a federação real é PUSH. Quem busca "pull" acha o doc de push.
4. **Errata-por-blockquote no corpo do ADR é anti-padrão.** Editar o corpo de um ADR aceito quebra a imutabilidade que protege o registro do raciocínio E não é sinal de máquina. O corpo deveria congelar; só o frontmatter muda.
5. **Template de ADR inconsistente.** 0001-0008 têm 0 cabeçalhos (prosa corrida); 0009-0012 têm 3-4 headers. Mata a escaneabilidade comparativa e a extração por seção nomeada — justo o que uma plataforma "recuperável por IA" mais deveria querer.
6. **Glossário canônico (CONTEXT.md) abriga termo morto e vivo lado a lado.** "Propósitos" coexiste com "Tipo" (que diz "substitui Propósito"); o _Avoid_ de "Tipo" lista "propósito" — o documento manda não usar uma palavra que ele mesmo define como cabeçalho. Falta uma seção "Termos aposentados".
7. **Wikilinks `[[arquivo]]` estilo Obsidian** (10 em CONTEXT.md) não renderizam como link no GitHub — viram texto com colchetes, quebrando o information scent (e degradando navegação por leitor de tela).
8. **README aponta para arquivos sabidamente desatualizados sem alerta real.** O "Mapa dos documentos" lista `taxonomia.md`/`exemplos-entradas.md` como equivalentes aos canônicos, com "(sessão 1)" no fim da linha como detalhe, não como aviso de "vocabulário obsoleto aqui".
9. **Não há lint/teste de consistência de vocabulário, nem CI de qualidade, nem linter de a11y, nem suíte de regressão XSS, nem runner de migração** (verificado). Toda a aposta é "vocabulário controlado" e "ingest determinístico", mas a disciplina é pedida a humanos sem ferramenta. A dívida de migração está como prosa dispersa (`arquitetura.md §9`), não como lista/runner rastreável.
10. **Duas taxonomias contraditórias sem função de tradução.** O lado TI (`tipos-ti.md` + arquitetura §4) lista ~10 tipos por eixo evergreen×datado; o lado Catálogo comprime em 5 propósitos. Nenhum doc dá a projeção FORMATO→MODO.
11. **A palavra "arquivar" não existe no desenho** (verificado). O vocabulário tem `obsoleto` como estado, mas não tem o CONCEITO de arquivo permanente (record preservado FORA da recuperação ativa). Só existem "continua presente" ou "foi deletado do git".
12. **Nenhum ADR contém a pergunta 2 do Shostack.** Zero seção de ameaças, zero DFD, zero tabela STRIDE, zero assunções datadas/checáveis. A fronteira mais perigosa — `repo de cliente → webhook → espelho` — nunca é desenhada. Mitigações afirmadas não têm a pergunta 4 (teste pareado).
13. **Nenhum campo de acessibilidade no schema inteiro** (verificado). Sem alt, long description, idioma, `title`; ADR-0012 nunca menciona o atributo `title` do iframe.
14. **Front-matter sem versão** (verificado). O ingest é binário (casa com hoje / inválido), sem "válido sob v1, precisa de upcast".
15. **Sem instrumentação de stragglers nem CDC.** O Brainiac não sabe quem ainda pina `RPQ:PRD-12@v2.0` nem qual módulo ainda emite `proposito`. O pin `@v2.0` é só uma string que ninguém valida. Todo CONTRACT é, hoje, um chute — e o ÍNDICE DE IA é o consumidor esquecido.

---

## Onde o Brainiac já acerta (defender — não over-corrigir)

- **Eixo de topo por PROPÓSITO/Tipo, não por departamento (ADR-0001).** Tese central compartilhada por Diátaxis, DITA, AI/KO e schema.org. Departamento como faceta plana evita silos e favorece recuperação. Decisão-âncora correta — não recuar.
- **Markdown canônico + render único com HTML como cache descartável (ADR-0010).** Consenso "single source of truth + render derivado" (Docs-as-Code/TechDocs/GitLab). A rodada 2 ADICIONA três defesas ao MESMO desenho: re-sanitizar sem re-ingestão (AppSec); ponto de controle único para HTML semântico/landmarks/axe-core (a11y); fonte limpa exportável para "transferir para arquivo" sem perda (records). É a melhor alavanca do projeto.
- **Metadado derivado do corpo no ingest determinístico (ADR-0005/0010).** Elimina por design o drift de "flags que mentem"; é o gancho perfeito para shift-left de a11y e upcast de schema.
- **id estável desacoplado de slug/título e recusa de cunhar id universal (ADR-0006).** Identidade estável bem aplicada e coerente com Backstage; facilita migração (não há id central a reescrever). A crítica é só o acoplamento à SIGLA mutável.
- **Inverter PULL→PUSH outbound (ADR-0009).** A AppSec DEFENDE explicitamente PUSH sobre PULL — eliminar o token org-wide e o inbound em N apps de prod é redução real de privilégio. A crítica é a pergunta 2 ausente, não a direção da seta.
- **Snapshot completo idempotente como TRANSPORTE (ADR-0009).** Idempotência é precondição de backfill seguro e de disposição. A engenharia de transporte está pronta para receber governança.
- **Doc UPSTREAM do rastreador, Monday como projeção descartável (ADR-0004).** Aplicação textbook de SSOT. Menos autoridade externa = janela de contract mais curta.
- **Freeze-on-publish do PRD + pilha congelada + referência por versão exata (ADR-0011).** Imutabilidade do contrato é a fonte de confiança. Reconhecido como APPRAISAL bem feito (records) e fronteira certa entre contrato pinado e classificação mutável. Defender contra "edição rápida in-place".
- **Artefato em iframe sandbox de origem separada SEM `allow-same-origin` (ADR-0012).** A AppSec DEFENDE o princípio central — recusar injeção inline e isolar JS cross-origin é correto (OWASP). Não inverter. A crítica é complementar (fixar flags, declarar CSP, `title`).
- **Vocabulário CONTROLADO de baixa cardinalidade + lista fechada.** É o que TORNA POSSÍVEL diffar schemas e nomear refatorações; campos estruturados fechados são pré-condição de "disposição como regra de máquina" (ISO 15489). A higiene já existe — falta usá-la nos eixos retenção e versão.
- **Coleção como view curada + relacionamentos JÁ tipados** (`relacionadas`/`depende_de`/`parte_de`/`substitui`↔`substituida_por`). Grafo tipado embrionário acima da média; `substitui` casado com `status: obsoleto` é PRECISAMENTE o padrão de supersessão da disciplina de records. A crítica é o que ele NÃO cobre (órfãos).
- **Recusar IA não-determinística dentro do sistema no v1 (sem embeddings/triplestore agora, reversível).** Calibragem madura. Ingest 100% determinístico é a base correta para upcast repetível. A crítica é não adiar a busca textual junto.
- **Reconhecer honestamente o ATRITO 1 e o custo da governança social (ADR-0008).** O time usa casos que doem e não finge que o sistema garante. ADR-0008 é o ÚNICO ADR que escreve um RISCO ACEITO explícito e revisável — padrão correto de risk acceptance. Falta só nomear o aceitante e separar o plano humano do plano de tokens.

---

## Temas transversais

1. **DISPOSIÇÃO é a metade do ciclo de vida que o Brainiac inteiro ignorou** (frente dominante da rodada 2: Records/ISO 15489 + AI-native). O projeto foi desenhado com esmero para CRIAR e USAR, mas APPRAISAL e DISPOSIÇÃO não existem. `obsoleto` é estado terminal sem transição de saída; a pilha do PRD só cresce; não há classe de retenção, gatilho de revisão, conceito de "arquivar", nem dono da disposição. A patologia é DISTINTA de staleness: é VOLUME DE RUÍDO ATIVO competindo com sinal, crescendo no tempo mesmo com cada doc fresco. A engenharia (snapshot idempotente, markdown limpo, ingest determinístico, vocabulário fechado) está PRONTA para receber a regra; falta a regra.
2. **SEGURANÇA foi tratada como checkbox, não como modelagem de ameaça** (STRIDE/Shostack/OWASP). Nenhum ADR escreve a pergunta 2 ("o que pode dar errado") nem a 4 ("como validamos"). A costura mais perigosa — repo de cliente → webhook → espelho — nunca é desenhada como fronteira de confiança. A escolha PUSH e o iframe sandbox estão CERTOS no princípio — falta a disciplina de ameaça em volta deles.
3. **ACESSIBILIDADE é propriedade ESTRUTURAL que o schema/render deveria garantir** (WCAG/POUR/Plain Language), e o desenho não tem um único campo dela. A Entrada só-artefato (caso dominante para Design/Marketing) pode ser totalmente inacessível. A11y fica 100% à mercê da disciplina do autor — quando o ingest determinístico e o render único são a alavanca de shift-left perfeita e desperdiçada. Mesmo padrão das outras frentes: o ponto de controle existe, a invariante não foi escrita.
4. **EVOLUÇÃO DE SCHEMA não tem modelo**: toda mudança de forma é evento ad hoc, não migração em fases. Sem `schema_version` (ingest binário), reconciliação travada no EXPAND, sigla imutável sem rota de rename, `departamento` 1→N sem mecânica, sem instrumentação de stragglers nem CDC. O projeto, com ~20 arquivos, JÁ falhou em se manter consistente.
5. **O FIO CONDUTOR das quatro frentes novas é IDÊNTICO ao da rodada 1**: o Brainiac tem a INFRA e a HIGIENE certas mas não escreveu a INVARIANTE em cima delas — seja a regra de disposição, a tabela STRIDE, o gate de a11y ou a versão de schema. E não come a própria comida: o resíduo das duas sessões é, simultaneamente, violação de SSOT, expand-sem-contract, e obsoleto-marcado-mas-não-disposto — prova viva, em três lentes, de que disciplina humana pura já falhou aqui.
6. **A taxonomia de 5 "propósitos" não é MECE** e colapsa três eixos num só: `decisão` é subespécie de explicação, `processo` é departamento disfarçado, falta `tutorial` (o modo que serve o onboarding, que o próprio Brainiac modela como Coleção ordenada).
7. **Frescor é estrutural, não comportamental**, e segue sem mecanismo anti-rot: gatilho de publish manual, badge passivo sem dono, `revisao_ate` descartado, status como sinal social sem registro. O obsoleto ÓRFÃO (sem sucessor) é a maior fonte de rot e jamais é apanhado por status+supersede — é o esquecimento que enche o corpus.
8. **A promessa "recuperável por IA" está vazia no v1**: filtro sobre vocabulário controlado é pré-filtro de escopo, não recuperação. Sem FTS sobre o corpo, sem chunk, sem ranking, sem llms.txt. Ligar FTS sem antes acoplar a regra de disposição só faz o ruído acumulado competir melhor — recuperação e disposição precisam ser desenhadas juntas.
9. **Identidade e single-source pela metade**: id estável amarrado à sigla MUTÁVEL (semantic rot embutido), e reúso-por-referência praticado no eixo PRD→spec mas ausente no eixo regra→N-PRDs. Ambos carecem da mesma coisa: rota de mudança em fases com alias e instrumentação de quem consome a forma antiga.
10. **O nível de abstração é "documento", não "ecossistema de software com dono"**: `module` é string, `owner` é texto, sem entidade Time/Pessoa/API nem grafo navegável. Faltam dois papéis: o DONO DA DISPOSIÇÃO (records manager) e o CONSUMIDOR instrumentado. Falta a camada de maturidade (scorecards) e a contribuição de baixo atrito.

---

## Fontes e leituras recomendadas

### Diátaxis / writing-for-task
- Daniele Procida — Diátaxis (home): https://diataxis.fr/
- The compass: https://diataxis.fr/compass/
- The difference between a tutorial and a how-to guide: https://diataxis.fr/tutorials-how-to/
- Explanation: https://diataxis.fr/explanation/
- Foundations: https://diataxis.fr/foundations/
- Hillel Wayne — My Problem With the Four-Document Model: https://www.hillelwayne.com/post/problems-with-the-4doc-model/
- Google Technical Writing One: https://developers.google.com/tech-writing/one
- Google developer documentation style guide: https://developers.google.com/style
- Make a README: https://www.makeareadme.com/
- Federal Plain Language Guidelines: https://www.plainlanguage.gov/guidelines/

### Docs-as-Code / handbook-first (SSOT)
- Write the Docs — Docs as Code: https://www.writethedocs.org/guide/docs-as-code/
- Cloudflare — Working in public (docs-as-code): https://blog.cloudflare.com/our-docs-as-code-approach/
- Netlify — Docs Linting in CI/CD: https://www.netlify.com/blog/a-key-to-high-quality-documentation-docs-linting-in-ci-cd/
- Grafana — Lint prose with Vale: https://grafana.com/docs/writers-toolkit/review/lint-prose/
- DocsAlot — Documentation Rots: https://docsalot.dev/blog/documentation-rots-heres-how-to-stop-it
- GitLab — Handbook-first: https://handbook.gitlab.com/handbook/company/culture/all-remote/handbook-first/
- GitLab — Shared Reality (TeamOps): https://handbook.gitlab.com/teamops/shared-reality/
- GitLab — Documentation Style Guide (preventing duplication): https://docs.gitlab.com/development/documentation/styleguide/
- 37signals — Guide to Internal Communication: https://37signals.com/how-we-communicate
- Basecamp handbook (repo público): https://github.com/basecamp/handbook
- Notion — docs-first culture: https://www.notion.com/help/guides/build-a-docs-first-culture-with-a-beautiful-team-wiki-powered-by-a-database

### DITA / single-sourcing
- DITA (OASIS / overview): https://en.wikipedia.org/wiki/Darwin_Information_Typing_Architecture
- conref — OASIS Architectural Spec: https://docs.oasis-open.org/dita/v1.2/os/spec/archSpec/conref.html
- keyref / conkeyref — OASIS: https://docs.oasis-open.org/dita/v1.2/cd03/spec/archSpec/keyref.html
- Oxygen DITA Style Guide — Chunking: https://www.oxygenxml.com/dita/styleguide/Topics_and_Information_Types/c_How_Chunking_Works.html
- Oxygen — The content reference (conref) attribute: https://www.oxygenxml.com/dita/styleguide/Content_Reuse/c_Content_Reference.html
- Excosoft — Why is the result often a million little pieces?: https://www.excosoft.com/updates/blog/why-is-the-result-often-a-million-little-pieces.html
- Carroll (ed.) — The Nurnberg Funnel / Minimalism (MIT Press)
- Heretto — Concept, Task & Reference: https://www.heretto.com/blog/concept-task-reference

### Arquitetura da Informação / Organização do Conhecimento
- Rosenfeld, Morville, Arango — Information Architecture (4th ed.) ("polar bear book")
- ANSI/NISO Z39.19-2005: https://www.luciehaskins.com/resources/Z39-19-2005.pdf
- SKOS Reference (W3C): https://www.w3.org/TR/skos-reference/
- SKOS Primer (W3C): https://www.w3.org/TR/skos-primer/
- ISO 25964-1:2011 (Thesauri): https://www.iso.org/obp/ui/en/#!iso:std:53657:en
- Heather Hedden — Taxonomy Governance: https://www.hedden-information.com/taxonomy-governance/
- Donna Spencer — Card Sorting: https://rosenfeldmedia.com/books/card-sorting/
- Ranganathan PMEST: https://www.lisedunetwork.com/ranganathans-pmest-the-foundation-of-faceted-classification/
- Glushko — Faceted Classification (The Discipline of Organizing): https://berkeley.pressbooks.pub/tdo4p/chapter/faceted-classification/

### Grafos de conhecimento / Linked data / PIDs
- W3C — Cool URIs don't change: https://www.w3.org/Provider/Style/URI
- ARK Alliance — Identifier concepts and conventions: https://arks.org/about/identifier-concepts-and-conventions/
- ARK Identifier Scheme (IETF draft): https://www.ietf.org/archive/id/draft-kunze-ark-34.html
- Microsoft Research — GraphRAG: https://arxiv.org/abs/2404.16130
- schema.org/TechArticle: https://schema.org/TechArticle
- When to use Graphs in RAG: https://arxiv.org/pdf/2506.05690

### Tradição ADR
- Michael Nygard — Documenting Architecture Decisions: https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions
- MADR: https://adr.github.io/madr/
- adr-tools (Nat Pryce): https://github.com/npryce/adr-tools
- Log4brains: https://github.com/thomvaill/log4brains
- ThoughtWorks Radar — Lightweight ADRs: https://www.thoughtworks.com/en-us/radar/techniques/lightweight-architecture-decision-records
- Martin Fowler — ADR: https://martinfowler.com/bliki/ArchitectureDecisionRecord.html

### Backstage / Software Catalog
- System Model: https://backstage.io/docs/features/software-catalog/system-model/
- Descriptor Format: https://backstage.io/docs/features/software-catalog/descriptor-format/
- Well-known Relations: https://backstage.io/docs/features/software-catalog/well-known-relations/
- TechDocs Architecture: https://backstage.io/docs/features/techdocs/architecture/
- GitHub Discovery: https://backstage.io/docs/integrations/github/discovery/
- Search Concepts & Collators: https://backstage.io/docs/features/search/concepts/
- Spotify — Golden Paths: https://engineering.atspotify.com/2020/08/how-we-use-golden-paths-to-solve-fragmentation-in-our-software-ecosystem
- Soundcheck (Tracks/Checks/Facts): https://backstage.spotify.com/docs/plugins/soundcheck/core-concepts/tracks

### RFC / PEP / spec-as-contract
- PEP 1: https://peps.python.org/pep-0001/
- The Rust RFC Book: https://rust-lang.github.io/rfcs/
- IETF — RFCs (processo): https://www.ietf.org/process/rfcs/
- IESG — Processing of RFC Errata: https://www.ietf.org/about/groups/iesg/statements/processing-errata-ietf-stream/
- oasdiff (OpenAPI breaking changes): https://www.oasdiff.com/
- Aaron Turon — Refining Rust's RFCs: https://aturon.github.io/blog/2016/07/05/rfc-refinement/

### AI-native / RAG
- llmstxt.org (Jeremy Howard / Answer.AI): https://llmstxt.org/
- Anthropic — Introducing Contextual Retrieval: https://www.anthropic.com/news/contextual-retrieval
- AWS Prescriptive Guidance — Documentation best practices for RAG: https://docs.aws.amazon.com/prescriptive-guidance/latest/writing-best-practices-rag/best-practices.html
- Mintlify — How to improve LLM readability (GEO): https://www.mintlify.com/blog/how-to-improve-llm-readability
- Dataquest — Metadata Filtering and Hybrid Search: https://www.dataquest.io/blog/metadata-filtering-and-hybrid-search-for-vector-databases/
- Databricks — Chunking Strategies for RAG: https://community.databricks.com/t5/technical-blog/the-ultimate-guide-to-chunking-strategies-for-rag-applications/ba-p/113089

### Doc rot / freshness engineering
- Software Engineering at Google — cap. 10 Documentation: https://abseil.io/resources/swe-book/html/ch10.html
- Tom Johnson — Maintaining existing documentation: https://idratherbewriting.com/learnapidoc/docapis_doc_maintenance_processes.html
- Nielsen Norman Group — Content Inventory and Auditing 101: https://www.nngroup.com/articles/content-audits/
- Dosu — Score Documentation Freshness in CI: https://dosu.dev/blog/score-documentation-freshness-in-ci
- Oomph — Toss the ROT: https://www.oomphinc.com/insights/rot-analysis-content-strategy/
- DataHub — Continuous Context: Why AI Docs Decay: https://datahub.com/blog/continuous-context/

### Records Management / Information Governance
- ISO 15489-1:2016: https://www.iso.org/standard/62542.html
- DCC — ISO 15489 briefing: https://www.dcc.ac.uk/guidance/briefing-papers/standards-watch-papers/iso-15489
- Records life-cycle (SAA / Schellenberg): https://www2.archivists.org/glossary/citation/the-life-cycle-model-for-managing-records-as-articulated-by-theodore-schellenberg-
- Terry Cook — Macro-Appraisal (LAC): https://www.bac-lac.gc.ca/eng/services/government-information-resources/disposition/Documents/MacroappraisalPartA.pdf
- GARP / ARMA — Generally Accepted Recordkeeping Principles: https://www.pathlms.com/arma-international/pages/principles
- ARMA — Big Bucket Retention: https://magazine.arma.org/2018/12/big-bucket-retention-objectives-issues-outcomes/
- DoD 5015.02-STD: https://www.esd.whs.mil/portals/54/documents/dd/issuances/dodi/501502p.pdf
- MoReq2010 (overview): https://en.wikipedia.org/wiki/MoReq2
- Records Continuum (Upward/McKemmish, via Lucidea): https://lucidea.com/blog/the-records-continuum-model-bridging-the-gap/

### Security / AppSec / Supply-chain
- OWASP — Threat Modeling Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/Threat_Modeling_Cheat_Sheet.html
- Threat Modeling Manifesto: https://www.threatmodelingmanifesto.org/
- Shostack — 4 Question Frame: https://github.com/adamshostack/4QuestionFrame
- Microsoft — STRIDE: https://learn.microsoft.com/en-us/archive/msdn-magazine/2006/november/uncover-security-design-flaws-using-the-stride-approach
- OWASP — HTML5 Security Cheat Sheet: https://cheatsheetseries.owasp.org/cheatsheets/HTML5_Security_Cheat_Sheet.html
- SLSA framework (via Wiz): https://www.wiz.io/academy/application-security/slsa-framework
- SBOM (Cycode): https://cycode.com/blog/software-bill-of-materials/
- APIsec — Securing Webhook Endpoints: https://www.apisec.ai/blog/securing-webhook-endpoints-best-practices
- Hooklistener — Webhook Security Guide: https://www.hooklistener.com/learn/webhook-security-fundamentals

### Acessibilidade / WCAG
- WCAG 2.2 (W3C): https://www.w3.org/TR/WCAG22/
- WCAG 1.1.1 Non-text Content — Understanding: https://www.w3.org/WAI/WCAG22/Understanding/non-text-content.html
- Mermaid — Accessibility Options: https://mermaid.js.org/config/accessibility.html
- WebAIM — Semantic Structure: https://webaim.org/techniques/semanticstructure/
- EAA & EN 301 549 (compliance guide): https://www.acquia.com/blog/european-accessibility-act-and-en-301-549-your-complete-compliance-guide
- The A11Y Project — Accessible heading structure: https://www.a11yproject.com/posts/how-to-accessible-heading-structure/

### Migração de schema (expand/migrate/contract)
- Martin Fowler — Parallel Change: https://martinfowler.com/bliki/ParallelChange.html
- Ambler & Sadalage — Refactoring Databases: https://scottambler.com/refactoring-databases/
- Tim Wellhausen — Expand and Contract: https://www.tim-wellhausen.de/papers/ExpandAndContract/ExpandAndContract.html
- Pete Hodgson — Expand/Contract without a big bang: https://blog.thepete.net/blog/2023/12/05/expand/contract-making-a-breaking-change-without-a-big-bang/
- Xata — Schema changes with pgroll: https://xata.io/blog/pgroll-expand-contract
- Bytebase — gh-ost vs pt-online-schema-change: https://www.bytebase.com/blog/gh-ost-vs-pt-online-schema-change/
- Neo4j APOC — Rename labels/types/properties: https://neo4j.com/docs/apoc/current/graph-refactoring/rename-label-type-property/

---

## Apêndice — digests por tradição

Resumo do que cada frente pesquisou e o que trouxe ao confronto.

- **Diátaxis (Procida).** Documentação serve NECESSIDADES, não assuntos. Quatro modos (tutorial/how-to/reference/explanation) gerados por dois eixos ortogonais (ação×cognição; aquisição×aplicação); a "bússola" é teste de classificação operacional. Tutorial e how-to são irredutíveis. Trouxe: a refutação da MECE dos 5 propósitos, a ausência do modo `tutorial`, e o reenquadramento do ATRITO 1 (co-localização permitida, pureza por seção).

- **Docs-as-Code / Write the Docs.** Doc como software: texto plano em Git, revisão por PR, CI/CD que valida e publica. Frescor por acoplamento de gatilho ao merge; review antes de publicar; CI como porteiro de qualidade; preview por PR; SSOT + render derivado. Trouxe: a crítica ao gatilho manual + badge passivo, a separação "CI como gatilho" vs "CI como porteiro", e o link-check/lint de prosa ausentes.

- **DITA / topic-based / single-sourcing.** A unidade é o tópico endereçável; "documento" é assembleia por mapa; teste de qualidade: "ao mudar X, edito em quantos?" → sempre um. conref/keyref/warehouse topic. Trouxe: a ausência de transclusão (regra cross-feature copiada em N PRDs), a Coleção que remonta links mas não conteúdo, e a interação congelamento×reúso não-pensada.

- **AI / Organização do Conhecimento.** Classificação facetada (Ranganathan), vocabulário controlado, SKOS (prefLabel/altLabel/hiddenLabel, polihierarquia, relações associativas, mapeamentos de evolução), garantia literária, card sorting. Trouxe: `departamento` cardinalidade 1 fundindo dono com assunto, conceito=rótulo=id (renomear quebra, sem sinônimos), governança de vocabulário inexistente, validação empírica ausente.

- **Grafos de conhecimento / Linked data / GraphRAG.** Sentido nas arestas tipadas entre entidades com PIDs estáveis e opacos. SKOS broader/narrower vs related (disjuntos); schema.org @id/isPartOf; GraphRAG percorre o grafo. Trouxe: semantic rot do id amarrado à sigla, ausência de eixo hierárquico e de disjunção declarada, e a recuperação como filtro (não travessia).

- **Tradição ADR (Nygard/MADR/adr-tools/Log4brains).** A unidade é a DECISÃO: registro pequeno, imutável, numerado, com Status de vocabulário fechado e supersessão bidirecional. Trouxe: os 12 ADRs sem Status, errata-por-blockquote, template inconsistente, ADR-0002 que mente, e o Brainiac não comer a própria comida.

- **Backstage / Software Catalog.** Catálogo de ENTIDADES com dono, relações DERIVADAS de fontes canônicas, descoberta contínua, busca unificada, Golden Paths/Soundcheck. Trouxe: o nível de abstração errado (documento, não ecossistema), `owner`/`module` como string, relações mantidas à mão, e a ausência de camada de maturidade.

- **GitLab Handbook / SSOT literal.** "Há só a versão"; duplicação é defeito a eliminar via link; otimizar recuperação; toda mudança é MR revisável; SSOT mantido por ritual. Trouxe: PRD/Spec como duas realidades editáveis, o resíduo das duas sessões como violação viva de SSOT, e o efêmero (Slack/reuniões) não-combatido.

- **Engenharia de release / agregação multi-repo (Kubernetes, Rust, Stripe, Antora, Docusaurus).** Versão de produto ≠ versão de doc; doc vive além do release; deltas inline; versionar é caro; cross-ref por identidade. Trouxe: a doc de TI tratada como snapshot estático, ausência de seletor de versão, e versionamento sem mecanismo de coexistência.

- **RFC / PEP / spec-as-contract.** Documento com ciclo de vida explícito; separar registro arquivístico (imutável) de spec viva; errata/Updates/Obsoletes; estado intermediário; janela de objeção (FCP). Trouxe: o falso binário rascunho/congelado, ausência de errata, e congelamento unilateral por uma pessoa só.

- **AI-native / RAG.** A unidade é o CHUNK recuperável; chunk decontextualizado é perdido; busca híbrida (semântica+lexical+reranking); llms.txt; context engineering. Trouxe: "recuperável por IA" como só pré-filtro, ausência de chunk/ranking/llms.txt, artefato opaco ao retriever, e o acoplamento FTS↔disposição.

- **Doc rot / freshness engineering.** Doc rot é o estado-padrão; "last reviewed" ≠ "last modified"; frescor é contrato com prazo (TTL por tipo); remoção ativa (ROT) é etapa; owner é papel, não pessoa. Trouxe: `revisao_ate` descartado, status sem gatilho, owner-pessoa como bomba-relógio, e o obsoleto órfão.

- **Writing for the reader's task (Google/Diátaxis/Stripe).** Estrutura ditada pela intenção do leitor; um documento, um trabalho; vocabulário 100% consistente; descoberta por navegação + busca. Trouxe: o glossário que abriga termo morto e vivo, os exemplos que usam o id proibido, o README que aponta para arquivos desatualizados sem alerta, e a ausência de lint de vocabulário.

- **Records Management / Information Governance (ISO 15489, Schellenberg, DoD 5015.2, MoReq2010, macro-appraisal).** Documentação é ativo com ciclo de vida COMPLETO e governado; disposição é metade do ciclo; appraisal atribui valor; reter e destruir são deveres simétricos. Trouxe: a frente dominante da rodada 2 — `obsoleto` como estado terminal morto, ausência de retenção/disposição/arquivo, o corpus monotônico que envenena a IA, e a ausência de dono da disposição e de legal hold.

- **Security / AppSec (STRIDE, Shostack, OWASP, SLSA/SBOM).** Documentação como modelo de ameaças: o que se constrói, o que pode dar errado, o que se faz, como se valida; fronteiras de confiança explícitas. Trouxe: a pergunta 2 ausente em todo ADR, deleção propaga como vetor de DoS, stored-XSS via markdown, webhook sem anti-replay/binding/auditoria, e o módulo onipresente como supply-chain única.

- **Acessibilidade / WCAG / Plain Language.** POUR; equivalente textual obrigatório (1.1.1); estrutura determinável programaticamente; HTML semântico antes de ARIA; acessibilidade por design/shift-left; exposição legal (EAA/EN 301 549). Trouxe: zero campos de a11y no schema, artefato só-JS inacessível, mermaid sem accDescr, iframe sem `title`, e a alavanca de shift-left (ingest+render) desperdiçada.

- **Migração de schema (expand/migrate/contract).** A estrutura é schema versionado e vivo; toda mudança de forma é migração em fases retrocompatíveis; backfill é passo separado; instrumentar stragglers antes de contrair; consumer-driven contracts. Trouxe: ausência de `schema_version` (ingest binário), a reconciliação travada no EXPAND, a sigla sem rota de rename, o `departamento` 1→N sem mecânica, e o ÍNDICE DE IA como consumidor esquecido.
