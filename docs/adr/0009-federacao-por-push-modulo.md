# Federação por PUSH: o módulo de doc publica no Brainiac via comando

O Brainiac recebe a doc de TI por **PUSH**, não por PULL: o **módulo de
documentação** (instalado em todo projeto) expõe um comando `docs:publish` que lê
`docs/**.md`, valida o front-matter e faz `POST` de um **snapshot completo**
(markdown + metadado) para um **único webhook de entrada** no Brainiac, autenticado
por **token por projeto + assinatura HMAC**. O gatilho é **manual/explícito**,
rodado a partir de um `main` limpo (o comando se recusa fora do main). A topologia
de dois andares ([ADR-0002](0002-topologia-hibrida.md)) e o princípio "só a
doc sai, o código nunca sai" permanecem; quem renderiza o markdown é o Brainiac
([ADR-0010](0010-markdown-canonico-render-centralizado.md)).

## Por que push, e não pull

PULL — o Brainiac consultar cada repo — esbarra em três fatos: os repos são
**privados**, o `/docs` roda **só em DEV**, e exigiria **uma das duas credenciais
caras**: ou um **token do GitHub org-wide** (que lê todo o código), ou o **Brainiac
alcançar cada app de prod** (rede privada/VPN) somado a um **registro das rotas** de
todos os projetos. Inverter a seta para PUSH elimina os três problemas: o app faz
uma chamada **outbound** para **uma** URL e se anuncia sozinho (o payload traz a
`sigla`).

## Opções consideradas

- **A — Brainiac puxa via GitHub API + webhook.** Exige token org-wide que lê todo
  o código; trava no GitHub; o Brainiac reimplementa discovery/parse. Rejeitada:
  credencial de privilégio alto e lógica duplicada.
- **B — CI do repo empurra no merge.** Acopla a doc à saúde do pipeline e exige
  workflow por repo. Rejeitada.
- **C — Brainiac puxa do `/api/docs` do módulo em prod.** Dispensa o token do GH,
  mas exige o Brainiac alcançar **cada** app de prod (inbound em N alvos) + um
  **registro de endpoints**. Rejeitada pela alcançabilidade e pelo registro de rotas.
- **D — o módulo empurra via comando (esta).** Outbound (1 URL), sem token do GH,
  sem CI, sem registro de rotas, sem poll; a lógica de parse vive no módulo
  compartilhado, então o Brainiac fica fino.

## Consequências

- **Sem** token do GitHub, **sem** step de CI, **sem** registro de endpoints,
  **sem** o Brainiac alcançar apps (só outbound).
- **Snapshot completo** a cada publish → idempotente; **deleção de doc propaga**.
- Gatilho manual ⇒ risco de espelho velho. Mitigação **sem** poll/CI: o Brainiac
  exibe **"última sincronização: há X dias"** por projeto, deixando a defasagem de
  sync visível.
- `docs:publish` envia **tudo**, independente do `status` de cada doc; o badge
  (`rascunho`/`revisão`/`publicado`) viaja junto — o `status` continua **sinal
  social**, não filtro de sync.
- Frescor = **estado merjado** (publica no merge, sem esperar deploy) — o semântico
  certo para spec/ADR, que viram verdade ao merjar.
- Depende do **módulo instalado** + **um secret por projeto**.
