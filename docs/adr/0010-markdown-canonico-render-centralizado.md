# Markdown é o formato canônico no Brainiac; o render é centralizado

O Brainiac guarda o **conteúdo do Documento como markdown** — formato canônico
único para o **PRD nativo** e para a **doc de TI espelhada** — e é o **único
renderizador** (markdown → HTML, sob demanda na leitura, com cache). O HTML é
**cache derivado, descartável**, nunca a fonte. O publicador envia **markdown +
metadado** (não HTML renderizado), e quem renderiza é o Brainiac; o push pelo
módulo ([ADR-0009](0009-federacao-por-push-modulo.md)) entrega a fonte.

## Por que

O Brainiac vai ter um renderizador **de qualquer jeito** — o PRD nasce aqui e
precisa ser exibido. Se o publicador também renderizar, passa a haver **dois
renderizadores** produzindo **dois visuais** para o mesmo catálogo (a doc de TI
com a cara do repo, o PRD com a cara do Brainiac). Markdown canônico + um
renderizador único colapsa isso em um só visual e ainda habilita: busca full-text
sobre a fonte limpa, troca de tema/engine **sem re-ingestão**, e **projeção
barata** para outros formatos (markdown/JSON para a IA do TI, HTML para humanos).

## Consequências

- **Payload do webhook = markdown + metadado + ponteiro git** (não HTML).
- **Render-on-read memoizado** (cache por hash do conteúdo): trocar tema/engine =
  limpar cache, sem migração; um PRD em `rascunho`, editado à vontade, só renderiza
  quando alguém abre.
- **Metadado não descreve o corpo.** O *authored* (front-matter) carrega só
  classificação/relações; fatos do corpo (tem imagem? tem mermaid? links/menções)
  são **derivados no ingest** pelo parser, **nunca** escritos à mão — evita o drift
  de um flag que mente quando o texto muda.
- **Stack:** `league/commonmark` (GFM + heading-anchors) + `tempest/highlight`
  (realce server-side, sem JS/API externa) + sanitização da saída; `mermaid.js`
  client-side carregado **só** nas páginas que emitem diagrama.
- **Imagens:** doc nativa usa o upload do Filament (disco → URL); doc de TI com
  caminho **relativo** de repo privado fica **adiada** (v1 do espelho só com URL
  absoluta já hospedada).
- **Rota para a IA** serve markdown/JSON **direto da fonte** (sem render) — mais
  barata que a página humana.

## Opções consideradas

- **HTML canônico (publicador renderiza, Brainiac guarda HTML).** Rejeitada: dois
  visuais no catálogo, perde busca sobre a fonte e re-tema livre, e de HTML não se
  recupera markdown limpo.
- **Salvar em vários formatos (md + html + json).** Rejeitada: dívida de
  sincronização e drift; guarda-se markdown e **projeta-se** o resto sob demanda.
