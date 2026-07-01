# Fontes e tradições da pesquisa

Reúne as **leituras** (com URL) e um **digest** do que cada tradição trouxe ao
confronto com o desenho do Brainiac. É o material de apoio dos temas em
[temas/](temas/).

---

## Digests — o que cada tradição trouxe

- **Diátaxis (Procida).** Documentação serve a NECESSIDADES, não a assuntos. Quatro
  modos (tutorial/how-to/reference/explanation) gerados por dois eixos (ação×cognição;
  aquisição×aplicação). Trouxe: a refutação da exclusividade dos cinco propósitos, a
  ausência do modo `tutorial`, e o reenquadramento do ATRITO 1 (co-localização
  permitida, pureza por seção). → tema [Tipos de documento](temas/1-tipos-de-documento.md).

- **Docs-as-Code / Write the Docs.** Doc como software: texto plano em Git, revisão por
  PR, CI/CD que valida e publica; frescor por acoplamento do gatilho ao merge; CI como
  porteiro de qualidade. Trouxe: a crítica ao gatilho manual + selo passivo, e a
  ausência de checagem de links/lint. → tema [Polir o que existe](temas/4-polir-o-que-existe.md).

- **DITA / topic-based / single-sourcing.** A unidade é o tópico endereçável; "documento"
  é montagem por mapa; teste de qualidade: "ao mudar X, edito em quantos arquivos?" →
  sempre um. conref/keyref. Trouxe: a ausência de transclusão (regra cross-feature
  copiada em N PRDs). → tema [Tipos de documento](temas/1-tipos-de-documento.md).

- **Arquitetura da informação / organização do conhecimento.** Classificação facetada
  (Ranganathan), vocabulário controlado, SKOS (prefLabel/altLabel, polihierarquia),
  garantia literária, card sorting. Trouxe: `departamento` cardinalidade 1 fundindo dono
  com assunto, conceito=rótulo=id, governança de vocabulário inexistente. → temas
  [Tipos de documento](temas/1-tipos-de-documento.md) e [Evolução e migração](temas/3-evolucao-e-migracao.md).

- **Grafos de conhecimento / linked data / PIDs.** Sentido nas arestas tipadas entre
  entidades com identificadores estáveis e opacos. Trouxe: o "semantic rot" do id
  amarrado à sigla, e a recuperação como filtro (não travessia). → tema
  [Evolução e migração](temas/3-evolucao-e-migracao.md).

- **Tradição ADR (Nygard/MADR/adr-tools/Log4brains).** A unidade é a DECISÃO: registro
  pequeno, imutável, numerado, com status de vocabulário fechado e supersessão
  bidirecional. Trouxe: os ADRs sem status, template inconsistente, e o Brainiac não
  comer a própria comida. → tema [Polir o que existe](temas/4-polir-o-que-existe.md).

- **Backstage / Software Catalog.** Catálogo de ENTIDADES com dono, relações DERIVADAS
  de fontes canônicas, descoberta contínua, busca unificada. Trouxe: o nível de abstração
  errado (documento, não ecossistema), `owner`/`module` como string. → temas
  [Polir o que existe](temas/4-polir-o-que-existe.md) e [Frescor e disposição](temas/2-frescor-e-disposicao.md).

- **GitLab Handbook / SSOT literal.** "Há só a versão"; duplicação é defeito a eliminar
  via link. Trouxe: PRD/Spec como duas realidades, e o resíduo das duas sessões como
  violação viva de SSOT. → tema [Polir o que existe](temas/4-polir-o-que-existe.md).

- **Engenharia de release / multi-repo (Kubernetes, Rust, Stripe, Antora).** Versão de
  produto ≠ versão de doc; doc vive além do release; cross-ref por identidade. Trouxe: a
  doc de TI tratada como snapshot estático. → tema [Evolução e migração](temas/3-evolucao-e-migracao.md).

- **RFC / PEP / spec-as-contract.** Ciclo de vida explícito; separar registro
  arquivístico (imutável) de spec viva; errata/Updates/Obsoletes; estado intermediário;
  janela de objeção (FCP). Trouxe: o falso binário rascunho/congelado do PRD, ausência de
  errata. → tema [Tipos de documento](temas/1-tipos-de-documento.md).

- **AI-native / RAG.** A unidade é o CHUNK recuperável; busca híbrida
  (semântica+lexical+reranking); llms.txt. Trouxe: "recuperável por IA" como só
  pré-filtro, e o acoplamento busca↔disposição. → tema [Recuperação por IA](temas/5-recuperacao-por-ia.md).

- **Doc rot / freshness engineering.** Doc rot é o estado-padrão; "last reviewed" ≠
  "last modified"; frescor é contrato com prazo (TTL por tipo); owner é papel, não pessoa.
  Trouxe: `revisao_ate` descartado, status sem gatilho, o obsoleto órfão. → tema
  [Frescor e disposição](temas/2-frescor-e-disposicao.md).

- **Writing for the reader's task (Google/Diátaxis/Stripe).** Estrutura ditada pela
  intenção do leitor; um documento, um trabalho; vocabulário 100% consistente. Trouxe: o
  glossário que abrigava termo morto e vivo, o README que apontava para arquivos
  desatualizados. → tema [Polir o que existe](temas/4-polir-o-que-existe.md).

- **Records management / information governance (ISO 15489, Schellenberg, DoD 5015.2,
  MoReq2010, macro-appraisal).** Documentação é ativo com ciclo de vida COMPLETO e
  governado; disposição é metade do ciclo; reter e destruir são deveres simétricos.
  Trouxe: a frente dominante da rodada 2 — `obsoleto` como estado terminal morto, ausência
  de retenção/disposição/arquivo, o corpus que envenena a IA. → tema
  [Frescor e disposição](temas/2-frescor-e-disposicao.md).

- **Security / AppSec (STRIDE, Shostack, OWASP, SLSA/SBOM).** Documentação como modelo de
  ameaças: o que se constrói, o que pode dar errado, o que se faz, como se valida. Trouxe:
  a pergunta "o que pode dar errado" ausente, deleção propaga como vetor de DoS,
  stored-XSS via markdown. → tema [Segurança](temas/6-seguranca.md).

- **Acessibilidade / WCAG / plain language.** POUR; equivalente textual obrigatório;
  estrutura programaticamente determinável; HTML semântico antes de ARIA; shift-left.
  Trouxe: zero campos de a11y no schema, artefato só-JS inacessível. → tema
  [Acessibilidade](temas/7-acessibilidade.md).

- **Migração de schema (expand/migrate/contract).** A estrutura é schema versionado e
  vivo; toda mudança de forma é migração em fases retrocompatíveis; backfill é passo
  separado; instrumentar quem usa a forma antiga antes de removê-la. Trouxe: ausência de
  `schema_version`, a reconciliação travada no meio, a sigla sem rota de rename. → tema
  [Evolução e migração](temas/3-evolucao-e-migracao.md).

---

## Leituras (com URL)

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

### Arquitetura da informação / organização do conhecimento
- Rosenfeld, Morville, Arango — Information Architecture (4th ed.) ("polar bear book")
- ANSI/NISO Z39.19-2005: https://www.luciehaskins.com/resources/Z39-19-2005.pdf
- SKOS Reference (W3C): https://www.w3.org/TR/skos-reference/
- SKOS Primer (W3C): https://www.w3.org/TR/skos-primer/
- ISO 25964-1:2011 (Thesauri): https://www.iso.org/obp/ui/en/#!iso:std:53657:en
- Heather Hedden — Taxonomy Governance: https://www.hedden-information.com/taxonomy-governance/
- Donna Spencer — Card Sorting: https://rosenfeldmedia.com/books/card-sorting/
- Ranganathan PMEST: https://www.lisedunetwork.com/ranganathans-pmest-the-foundation-of-faceted-classification/
- Glushko — Faceted Classification (The Discipline of Organizing): https://berkeley.pressbooks.pub/tdo4p/chapter/faceted-classification/

### Grafos de conhecimento / linked data / PIDs
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

### Records management / information governance
- ISO 15489-1:2016: https://www.iso.org/standard/62542.html
- DCC — ISO 15489 briefing: https://www.dcc.ac.uk/guidance/briefing-papers/standards-watch-papers/iso-15489
- Records life-cycle (SAA / Schellenberg): https://www2.archivists.org/glossary/citation/the-life-cycle-model-for-managing-records-as-articulated-by-theodore-schellenberg-
- Terry Cook — Macro-Appraisal (LAC): https://www.bac-lac.gc.ca/eng/services/government-information-resources/disposition/Documents/MacroappraisalPartA.pdf
- GARP / ARMA — Generally Accepted Recordkeeping Principles: https://www.pathlms.com/arma-international/pages/principles
- ARMA — Big Bucket Retention: https://magazine.arma.org/2018/12/big-bucket-retention-objectives-issues-outcomes/
- DoD 5015.02-STD: https://www.esd.whs.mil/portals/54/documents/dd/issuances/dodi/501502p.pdf
- MoReq2010 (overview): https://en.wikipedia.org/wiki/MoReq2
- Records Continuum (Upward/McKemmish, via Lucidea): https://lucidea.com/blog/the-records-continuum-model-bridging-the-gap/

### Security / AppSec / supply-chain
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
