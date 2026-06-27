# Artefato: asset HTML referenciado por link, exibido em iframe isolado

Um **Artefato** é uma página visual **auto-contida** (HTML/CSS/JS) — front-end
arbitrário, o oposto do markdown. O Brainiac **não** o hospeda nem o parseia: guarda
o **link** e o exibe embutido num **iframe `sandbox` de origem isolada**. Em um
documento **com corpo**, o artefato entra como **link no próprio markdown** e o
Brainiac **deriva e embute** (coerente com [ADR-0010](0010-markdown-canonico-render-centralizado.md):
metadado não descreve o corpo); o campo explícito `artefatos` fica reservado à
Entrada **só-artefato** (sem corpo onde pôr o link). Hospedagem **externa**
(waifuvault) por enquanto.

## Por que

O artefato é **JS arbitrário de qualquer autor** (e queremos os times não-técnicos
produzindo). Injetá-lo inline numa página do Brainiac é **stored-XSS** — o script
alcançaria sessão/DOM do Brainiac; e o sanitizador do markdown **mataria** o JS que
é justamente o valor dele. A saída é servi-lo **intacto, isolado**: iframe `sandbox`
(sem `allow-same-origin`) servido de uma **origem separada**. Cross-origin já
bloqueia o acesso ao DOM nos dois sentidos; o `sandbox` limita o que o próprio
artefato pode fazer (sem navegar a página de cima, sem storage do Brainiac).

## Decisões

- **Exibição:** iframe isolado, como **card que expande** (lazy, pra não carregar
  iframes pesados à toa) + "abrir em tela cheia" (o link standalone).
- **Detecção:** por **host** — uma lista de origens de artefato; link daquela origem
  vira embed, link normal vira link normal. Regra determinística de ingest.
- **Onde o link mora:** no **corpo markdown** (doc com corpo) → "tem artefato" é
  **derivado**, não campo preenchido à mão. O campo `artefatos` é ponteiro de
  conteúdo **só** pra Entrada só-artefato. A invariante "≥1 conteúdo por Entrada"
  permanece.
- **Autoria:** a skill grill-me-with-docs pode perguntar "anexar um artefato ou
  melhorar a explicação?" e **inserir o link no corpo** — nunca um metadado à mão.
- **Hospedagem:** externa (waifuvault) agora. **Store da empresa** quando bater um
  destes gatilhos: controle de acesso, URL estável (hoje re-subir gera URL nova e
  órfã) ou atrelar à versão do PRD/spec.

## Consequências

- O Brainiac **nunca** hospeda nem parseia o HTML do artefato — só aponta e embute.
- O link do artefato **viaja dentro do markdown** no snapshot do TI; **sem** campo
  especial no webhook.
- Relação com mermaid: **workflow parecido** (mora no corpo, o Brainiac renderiza em
  contexto), **mecanismo oposto** (mermaid = SVG server-side seguro; artefato =
  iframe isolado por ser JS arbitrário).

## Opções consideradas

- **Inline (injetar o HTML do artefato na página).** Rejeitada: stored-XSS, e
  sanitizar mataria o JS/interatividade — o valor do artefato.
- **Campo `artefatos` preenchido à mão em todo doc.** Rejeitada: metadado
  descrevendo o corpo (gera drift); deriva-se do link no corpo.
- **Brainiac hospedar o HTML nativamente já.** Adiada: o waifuvault funciona; o
  store próprio só com gatilho real (acesso/URL estável/versão).
