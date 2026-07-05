<?php

declare(strict_types=1);

namespace He4rt\Portal\Database\Seeders;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;
use He4rt\Catalog\Models\EntryLink;
use He4rt\Catalog\Models\PrdVersion;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use Illuminate\Database\Seeder;

/**
 * Dados de demonstração do portal — espelham o protótipo aprovado no Claude
 * Design ("Catálogo Brainiac v2"): 3 projetos, 12 entradas, 2 trilhas,
 * ligações tipadas e a pilha de versões do PRD. Idempotente.
 */
final class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $camila = $this->user('Camila Duarte', 'camila@demo.brainiac');
        $felipe = $this->user('Felipe Ramos', 'felipe@demo.brainiac');
        $julia = $this->user('Júlia Menezes', 'julia@demo.brainiac');

        $brn = Project::query()->updateOrCreate(['acronym' => 'BRN'], [
            'business_name' => 'Brainiac',
            'technical_name' => 'brainiac',
            'slug' => 'brainiac',
            'repo_url' => 'https://github.com/3pontos/brainiac',
            'default_branch' => 'main',
            'last_synced_at' => null,
        ]);

        $pay = Project::query()->updateOrCreate(['acronym' => 'PAY'], [
            'business_name' => 'Pagamentos Core',
            'technical_name' => 'payments-core',
            'slug' => 'payments-core',
            'repo_url' => 'https://github.com/3pontos/payments-core',
            'default_branch' => 'main',
            'last_synced_at' => now()->subHours(2),
        ]);

        $rpq = Project::query()->updateOrCreate(['acronym' => 'RPQ'], [
            'business_name' => 'Rank & Query',
            'technical_name' => 'rank-query',
            'slug' => 'rank-query',
            'repo_url' => 'https://github.com/3pontos/rank-query',
            'default_branch' => 'main',
            'last_synced_at' => now()->subHours(2),
        ]);

        $e1 = $this->entry([
            'qualified_id' => 'RPQ:moderation/adr/0003', 'native_id' => 'moderation/adr/0003',
            'project_id' => $rpq->id, 'slug' => 'fila-unica-moderacao',
            'title' => 'Fila única para moderação de conteúdo',
            'summary' => 'Decisão de consolidar as três filas de moderação em uma única fila priorizada, eliminando starvation de itens de baixo volume.',
            'purpose' => Purpose::Explanation, 'format' => Format::Adr, 'origin' => Origin::Mirror,
            'department' => Area::Ti, 'audience' => [Audience::Ti],
            'keywords' => ['moderação', 'filas', 'sqs', 'prioridade'],
            'status' => Status::Published, 'owner_id' => null, 'authors' => ['ana-souza', 'rmarinho'],
        ], body: <<<'MD'
            ## Contexto

            A moderação de conteúdo operava com **três filas independentes** (texto, imagem e denúncias), cada uma com seu próprio pool de workers. Em picos de tráfego, a fila de denúncias — de baixo volume, mas alta criticidade — ficava sem workers disponíveis por horas.

            ## Decisão

            Consolidar as três filas em uma **fila única priorizada**, com peso por tipo de item:

            - Denúncias: prioridade `alta`
            - Imagem: prioridade `média`
            - Texto: prioridade `baixa`

            ```mermaid
            flowchart LR
              A[Ingestão] --> B{Fila única}
              B -->|alta| C[Workers]
              B -->|média| C
              B -->|baixa| C
            ```

            ## Consequências

            - O SLA de denúncias caiu de horas para **menos de 4 minutos** no P95.
            - O throughput de texto reduziu ~8% em picos — aceito pelo time de Negócio.
            - A telemetria de fila passa a ter uma única dimensão `priority`, simplificando os dashboards.

            ## Alternativas consideradas

            1. Autoscaling por fila — descartado pelo custo de workers ociosos.
            2. Roubo de trabalho entre pools — complexidade alta de coordenação.
            MD, gitPointer: 'docs/adr/0003-fila-unica-moderacao.md');

        $e2 = $this->entry([
            'qualified_id' => 'RPQ:search/architecture/overview', 'native_id' => 'search/architecture/overview',
            'project_id' => $rpq->id, 'slug' => 'arquitetura-busca',
            'title' => 'Arquitetura do serviço de busca',
            'summary' => 'Visão geral dos componentes do serviço de busca: ingestão, indexação incremental, ranking e a API de consulta.',
            'purpose' => Purpose::Explanation, 'format' => Format::Architecture, 'origin' => Origin::Mirror,
            'department' => Area::Ti, 'audience' => [Audience::Ti, Audience::Product],
            'keywords' => ['busca', 'elasticsearch', 'ranking'],
            'status' => Status::Published, 'owner_id' => null, 'authors' => ['rmarinho'],
        ], body: <<<'MD'
            ## Componentes

            O serviço de busca é composto por quatro blocos principais:

            1. **Ingestão** — consome eventos de domínio e normaliza documentos.
            2. **Indexação incremental** — aplica upserts no índice a cada 30s.
            3. **Ranking** — combina relevância textual com sinais de engajamento.
            4. **API de consulta** — camada HTTP com cache de 60s para consultas quentes.

            ```mermaid
            flowchart TD
              E[Eventos de domínio] --> I[Ingestão]
              I --> X[Indexação]
              X --> R[(Índice)]
              Q[API de consulta] --> R
              Q --> K[Ranking]
            ```

            ![Diagrama de contexto do serviço de busca](docs/img/search-context.png)

            ## Limites conhecidos

            - Reindexação completa leva ~40 min e exige janela de manutenção.
            - O ranking não considera sinais de personalização — planejado para o H2.
            MD, gitPointer: 'docs/architecture/overview.md');

        $e3 = $this->entry([
            'qualified_id' => 'PAY:core/readme', 'native_id' => 'core/readme',
            'project_id' => $pay->id, 'slug' => 'pagamentos-core-readme',
            'title' => 'Pagamentos Core',
            'summary' => 'Ponto de entrada do repositório payments-core: escopo do serviço, como rodar localmente e mapa dos módulos.',
            'purpose' => Purpose::Reference, 'format' => Format::Readme, 'origin' => Origin::Mirror,
            'department' => Area::Ti, 'audience' => [Audience::Ti],
            'keywords' => ['pagamentos', 'setup', 'onboarding'],
            'status' => Status::Published, 'owner_id' => null, 'authors' => ['dl-teixeira', 'ana-souza'],
        ], body: <<<'MD'
            ## O que é

            O `payments-core` processa cobranças, estornos e conciliação para todos os produtos da empresa. É a **única** integração com os adquirentes.

            ## Rodando localmente

            ```bash
            git clone git@github.com:3pontos/payments-core.git
            cd payments-core
            make up   # sobe Postgres + LocalStack
            make test
            ```

            ## Mapa de módulos

            - `charges/` — ciclo de vida da cobrança
            - `refunds/` — estornos totais e parciais
            - `webhooks/` — recepção e reprocessamento de eventos dos adquirentes
            - `recon/` — conciliação diária

            Antes de tocar em `charges/`, leia a spec de idempotência (PAY:core/spec/idempotencia).
            MD, gitPointer: 'README.md');

        $e4 = $this->entry([
            'qualified_id' => 'PAY:webhooks/how-to/retry', 'native_id' => 'webhooks/how-to/retry',
            'project_id' => $pay->id, 'slug' => 'reprocessar-webhooks',
            'title' => 'Reprocessar webhooks falhos',
            'summary' => 'Passo a passo para identificar, inspecionar e reprocessar webhooks de adquirentes que falharam na entrega.',
            'purpose' => Purpose::HowTo, 'format' => Format::HowTo, 'origin' => Origin::Mirror,
            'department' => Area::Ti, 'audience' => [Audience::Ti],
            'keywords' => ['webhooks', 'retry', 'incidente', 'runbook'],
            'status' => Status::Published, 'owner_id' => null, 'authors' => ['dl-teixeira'],
        ], body: <<<'MD'
            ## Quando usar

            Use este procedimento quando o alerta `webhooks.delivery_failed > 50/min` disparar ou quando um adquirente reportar eventos não confirmados.

            ## Passos

            1. Liste os webhooks falhos das últimas 6 horas:

            ```bash
            pay-cli webhooks list --status=failed --since=6h
            ```

            2. Inspecione um evento para confirmar que a falha não é de payload:

            ```bash
            pay-cli webhooks inspect <event_id>
            ```

            3. Reprocesse em lote, **sempre com dry-run primeiro**:

            ```bash
            pay-cli webhooks retry --status=failed --since=6h --dry-run
            pay-cli webhooks retry --status=failed --since=6h
            ```

            ## Cuidados

            - Nunca reprocesse eventos de `charge.captured` sem verificar duplicidade na conciliação.
            - Acima de 10 mil eventos, acione o time de plantão antes.
            MD, gitPointer: 'docs/how-to/retry-webhooks.md');

        $e5 = $this->entry([
            'qualified_id' => 'BRN:catalogo/prd/0001', 'native_id' => 'catalogo/prd/0001',
            'project_id' => null, 'slug' => 'prd-catalogo',
            'title' => 'PRD — Catálogo de documentação',
            'summary' => 'Definição do produto Brainiac: catálogo federado que salva e organiza a documentação de departamentos e projetos.',
            'purpose' => Purpose::Reference, 'format' => Format::Prd, 'origin' => Origin::Native,
            'department' => Area::Product, 'audience' => [Audience::Product, Audience::Ti, Audience::Design],
            'keywords' => ['catálogo', 'federação', 'diátaxis'],
            'status' => Status::Review, 'owner_id' => $camila->id, 'authors' => null,
        ]);
        $e5->projects()->syncWithoutDetaching([$brn->id]);
        EntryArtifact::query()->firstOrCreate(['entry_id' => $e5->id, 'url' => 'https://figma.com/file/brainiac-catalogo-v2']);
        $this->prdVersions($e5);

        $e6 = $this->entry([
            'qualified_id' => 'BRN:onboarding/how-to/publicar-doc', 'native_id' => 'onboarding/how-to/publicar-doc',
            'project_id' => null, 'slug' => 'publicar-doc',
            'title' => 'Publicar um documento no catálogo',
            'summary' => 'Guia para criar uma entrada nativa, classificá-la corretamente e levá-la de rascunho a publicado.',
            'purpose' => Purpose::HowTo, 'format' => Format::HowTo, 'origin' => Origin::Native,
            'department' => Area::Product, 'audience' => [Audience::All],
            'keywords' => ['publicação', 'fluxo', 'classificação'],
            'status' => Status::Published, 'owner_id' => $felipe->id, 'authors' => null,
        ], body: <<<'MD'
            ## Antes de começar

            Tenha claro **para que serve** o seu doc. Se você não consegue dizer se é referência, how-to ou explicação, leia primeiro a explicação do eixo Diátaxis.

            ## Passos

            1. Crie a entrada em **Entradas → Nova entrada**.
            2. Preencha título e um resumo de 1 a 3 frases — o resumo é o principal sinal de descoberta.
            3. Classifique propósito, formato, departamento e público.
            4. Escreva o corpo em Markdown.
            5. Mova o status para **Revisão** e marque um revisor do seu departamento.
            6. Aprovado? Publique.

            ![Fluxo de publicação](img/fluxo-publicacao.png)

            ## Boas práticas

            - Um doc, um propósito. Se está misturando tutorial com referência, divida.
            - Palavras-chave são para sinônimos que não aparecem no título.
            MD);
        $e6->projects()->syncWithoutDetaching([$brn->id]);

        $e7 = $this->entry([
            'qualified_id' => 'BRN:governanca/explanation/diataxis', 'native_id' => 'governanca/explanation/diataxis',
            'project_id' => null, 'slug' => 'diataxis',
            'title' => 'Por que organizamos docs pelo eixo Diátaxis',
            'summary' => 'Explica a escolha do framework Diátaxis como eixo de classificação e como propósito difere de formato.',
            'purpose' => Purpose::Explanation, 'format' => Format::Explanation, 'origin' => Origin::Native,
            'department' => Area::Business, 'audience' => [Audience::All],
            'keywords' => ['diátaxis', 'governança', 'taxonomia'],
            'status' => Status::Published, 'owner_id' => $julia->id, 'authors' => null,
        ], body: <<<'MD'
            ## O problema da pilha única

            Quando tudo é "documentação", o leitor não sabe o que esperar ao abrir um doc: é um passo a passo? Uma tabela de consulta? Uma justificativa histórica?

            ## Propósito ≠ formato

            No Brainiac, cada entrada tem dois eixos independentes:

            - **Propósito** — para que o doc serve: *referência*, *how-to* ou *explicação*.
            - **Formato** — a forma concreta: README, ADR, spec, PRD, plano…

            Um ADR tem formato `adr` e propósito `explicação`: ele explica uma decisão. Uma spec tem formato `spec` e propósito `referência`: você a consulta, não a lê de ponta a ponta.

            ## O que ganhamos

            - Filtros que respondem perguntas reais ("me dá todos os how-tos de TI").
            - Expectativa correta antes do clique.
            - Um vocabulário comum entre departamentos que escrevem docs muito diferentes.
            MD);
        $e7->projects()->syncWithoutDetaching([$brn->id]);

        $e8 = $this->entry([
            'qualified_id' => 'BRN:marketing/plan/lancamento-q3', 'native_id' => 'marketing/plan/lancamento-q3',
            'project_id' => null, 'slug' => 'lancamento-q3',
            'title' => 'Plano de lançamento interno — Q3',
            'summary' => 'Plano de comunicação e adoção do catálogo para o terceiro trimestre, por departamento.',
            'purpose' => Purpose::Reference, 'format' => Format::Plan, 'origin' => Origin::Native,
            'department' => Area::Marketing, 'audience' => [Audience::Marketing, Audience::Business],
            'keywords' => ['lançamento', 'adoção', 'comunicação'],
            'status' => Status::Draft, 'owner_id' => $julia->id, 'authors' => null,
        ]);
        $e8->projects()->syncWithoutDetaching([$brn->id]);

        $e9 = $this->entry([
            'qualified_id' => 'BRN:design/reference/tokens', 'native_id' => 'design/reference/tokens',
            'project_id' => null, 'slug' => 'design-tokens',
            'title' => 'Tokens de design do painel',
            'summary' => 'Tabela de referência dos tokens de cor, tipografia e espaçamento usados nos componentes do painel.',
            'purpose' => Purpose::Reference, 'format' => Format::Reference, 'origin' => Origin::Native,
            'department' => Area::Design, 'audience' => [Audience::Design, Audience::Ti],
            'keywords' => ['tokens', 'cores', 'tipografia'],
            'status' => Status::Published, 'owner_id' => $camila->id, 'authors' => null,
        ], body: <<<'MD'
            ## Cores

            - `primary` — roxo, usado em ações e navegação ativa
            - `info` / `success` / `warning` / `danger` — estados semânticos dos badges
            - `gray` — neutros de texto e superfícies

            ## Tipografia

            - UI: sans-serif do sistema, 14px base
            - Leitura longa: serif, 16.5px, entrelinha 1.7
            - Identificadores e metadados: monoespaçada, 11–12px

            ## Espaçamento

            Escala de 4px. Superfícies usam raio de 8 a 14px conforme o tamanho do contêiner.
            MD);
        $e9->projects()->syncWithoutDetaching([$brn->id]);
        EntryArtifact::query()->firstOrCreate(['entry_id' => $e9->id, 'url' => 'https://figma.com/file/brainiac-tokens']);

        $e10 = $this->entry([
            'qualified_id' => 'PAY:core/spec/idempotencia', 'native_id' => 'core/spec/idempotencia',
            'project_id' => $pay->id, 'slug' => 'spec-idempotencia',
            'title' => 'Spec de idempotência de cobranças',
            'summary' => 'Contrato de idempotência da API de cobranças: chaves, janelas de deduplicação e códigos de resposta.',
            'purpose' => Purpose::Reference, 'format' => Format::Spec, 'origin' => Origin::Mirror,
            'department' => Area::Ti, 'audience' => [Audience::Ti, Audience::External],
            'keywords' => ['idempotência', 'api', 'contrato'],
            'status' => Status::Published, 'owner_id' => null, 'authors' => ['ana-souza'],
        ], body: <<<'MD'
            ## Chave de idempotência

            Toda requisição `POST /charges` deve enviar o header `Idempotency-Key` (UUID v4). Requisições sem a chave são rejeitadas com `400`.

            ## Janela de deduplicação

            - A chave é válida por **24 horas**.
            - Reenvio com a mesma chave e o mesmo payload → `200` com a resposta original.
            - Reenvio com a mesma chave e payload **diferente** → `409 Conflict`.

            ## Garantias

            O armazenamento das chaves é no Postgres com TTL, não em cache — a deduplicação sobrevive a reinícios.
            MD, gitPointer: 'docs/spec/idempotency.md');

        $e11 = $this->entry([
            'qualified_id' => 'BRN:ti/context/plataforma', 'native_id' => 'ti/context/plataforma',
            'project_id' => null, 'slug' => 'mapa-plataforma',
            'title' => 'Mapa da plataforma interna',
            'summary' => 'CONTEXT do ecossistema: quais serviços existem, quem é dono de cada um e como se conectam.',
            'purpose' => Purpose::Reference, 'format' => Format::Context, 'origin' => Origin::Native,
            'department' => Area::Ti, 'audience' => [Audience::Ti, Audience::Product],
            'keywords' => ['plataforma', 'serviços', 'ownership'],
            'status' => Status::Published, 'owner_id' => $felipe->id, 'authors' => null,
        ], body: <<<'MD'
            ## Serviços e donos

            - **payments-core** (PAY) — time Pagamentos
            - **rank-query** (RPQ) — time Descoberta
            - **brainiac** (BRN) — time Plataforma de Conhecimento

            ```mermaid
            flowchart LR
              APP[Produtos] --> PAY[payments-core]
              APP --> RPQ[rank-query]
              BRN[brainiac] -. federação .-> PAY
              BRN -. federação .-> RPQ
            ```

            ## Convenções

            - Todo serviço expõe `/health` e publica eventos no barramento comum.
            - A documentação de cada serviço é espelhada no Brainiac via webhook de push.
            MD);
        $e11->projects()->syncWithoutDetaching([$brn->id, $pay->id, $rpq->id]);

        $e12 = $this->entry([
            'qualified_id' => 'BRN:produto/adr/0007', 'native_id' => 'produto/adr/0007',
            'project_id' => null, 'slug' => 'adr-wiki-central',
            'title' => 'Wiki central como repositório de docs',
            'summary' => 'Decisão antiga de centralizar docs numa wiki. Substituída pelo modelo federado do catálogo.',
            'purpose' => Purpose::Explanation, 'format' => Format::Adr, 'origin' => Origin::Native,
            'department' => Area::Product, 'audience' => [Audience::Product, Audience::Ti],
            'keywords' => ['wiki', 'histórico'],
            'status' => Status::Obsolete, 'owner_id' => $camila->id, 'authors' => null,
        ], body: <<<'MD'
            ## Decisão (superada)

            Centralizar toda a documentação numa wiki única, com migração manual dos docs dos repositórios.

            ## Por que foi abandonada

            - Docs de engenharia divergiam da fonte em semanas.
            - Ninguém mantinha a migração manual.

            Esta decisão foi **substituída** pelo modelo federado descrito no PRD do catálogo.
            MD);
        $e12->projects()->syncWithoutDetaching([$brn->id]);

        $this->link($e5, $e12, EntryLinkType::Supersedes);
        $this->link($e5, $e7, EntryLinkType::Related);
        $this->link($e6, $e7, EntryLinkType::DependsOn);
        $this->link($e4, $e3, EntryLinkType::PartOf);
        $this->link($e10, $e3, EntryLinkType::PartOf);
        $this->link($e1, $e2, EntryLinkType::Related);

        $onboarding = Collection::query()->updateOrCreate(['slug' => 'onboarding-devs'], [
            'title' => 'Onboarding de novos devs',
            'summary' => 'O caminho mínimo para uma pessoa dev entender a plataforma e conseguir contribuir na primeira semana.',
            'audience' => [Audience::Ti],
            'owner_id' => $felipe->id,
            'status' => Status::Published,
            'body_markdown' => 'Siga a trilha **em ordem**: primeiro o mapa da plataforma para entender o todo, depois os serviços que você vai tocar com mais frequência. Reserve cerca de duas horas.',
        ]);
        $onboarding->entries()->sync([
            $e11->id => ['position' => 1],
            $e3->id => ['position' => 2],
            $e2->id => ['position' => 3],
            $e4->id => ['position' => 4],
        ]);

        $governance = Collection::query()->updateOrCreate(['slug' => 'governanca-docs'], [
            'title' => 'Governança de documentação',
            'summary' => 'Como pensamos, classificamos e publicamos documentação na empresa — leitura obrigatória para quem escreve docs.',
            'audience' => [Audience::All],
            'owner_id' => $julia->id,
            'status' => Status::Published,
            'body_markdown' => 'Comece pelo *porquê* (Diátaxis), passe pelo *como* (publicação) e termine no PRD para entender aonde o produto vai.',
        ]);
        $governance->entries()->sync([
            $e7->id => ['position' => 1],
            $e6->id => ['position' => 2],
            $e5->id => ['position' => 3],
        ]);
    }

    private function user(string $name, string $email): User
    {
        $existing = User::query()->where('email', $email)->first();

        return $existing ?? User::factory()->create(['name' => $name, 'email' => $email]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function entry(array $attributes, ?string $body = null, ?string $gitPointer = null): Entry
    {
        $entry = Entry::query()->updateOrCreate(
            ['qualified_id' => $attributes['qualified_id']],
            $attributes,
        );

        if ($body !== null) {
            Document::query()->updateOrCreate(
                ['entry_id' => $entry->id],
                ['body_markdown' => $body, 'git_pointer' => $gitPointer],
            );
        }

        return $entry;
    }

    private function link(Entry $from, Entry $to, EntryLinkType $type): void
    {
        EntryLink::query()->firstOrCreate([
            'from_entry_id' => $from->id,
            'to_entry_id' => $to->id,
            'type' => $type,
        ]);
    }

    private function prdVersions(Entry $entry): void
    {
        $v21 = <<<'MD'
            ## Problema

            A documentação da empresa vive espalhada em repositórios, drives e wikis. Ninguém sabe **o que existe**, **o que vale** e **quem é o dono**. Docs de engenharia ficam nos repos; docs de negócio, em apresentações perdidas.

            ## Proposta

            Um catálogo único com duas origens de conteúdo:

            - **Nativo** — criado no próprio Brainiac, com dono e ciclo de vida.
            - **Espelho** — sincronizado dos repositórios via federação, sempre somente leitura.

            Toda entrada é classificada por propósito (eixo Diátaxis), formato, departamento e público — e é isso que alimenta a descoberta.

            ## Federação por webhook

            Cada repositório registra um webhook de push. A cada merge na branch padrão, o Brainiac reprocessa os docs alterados e atualiza os espelhos — a entrada carrega `git_pointer` para compor o "ver na fonte".

            ## Métricas de sucesso

            - 80% dos docs ativos catalogados em 2 trimestres.
            - Tempo médio para encontrar um doc < 60 segundos.
            - Zero docs publicados sem dono ou autores.

            ## Fora de escopo (v2.1)

            - Edição colaborativa em tempo real.
            - Comentários e discussões no leitor.
            MD;

        $v20 = <<<'MD'
            ## Problema

            A documentação da empresa vive espalhada em repositórios, drives e wikis. Ninguém sabe **o que existe**, **o que vale** e **quem é o dono**. Docs de engenharia ficam nos repos; docs de negócio, em apresentações perdidas.

            ## Proposta

            Um catálogo único com duas origens de conteúdo:

            - **Nativo** — criado no próprio Brainiac, com dono e ciclo de vida.
            - **Espelho** — sincronizado dos repositórios via federação, sempre somente leitura.

            Toda entrada é classificada por propósito (eixo Diátaxis), formato, departamento e público — e é isso que alimenta a descoberta.

            ## Métricas de sucesso

            - 80% dos docs ativos catalogados em 2 trimestres.
            - Tempo médio para encontrar um doc < 60 segundos.
            - Zero docs publicados sem dono ou autores.

            ## Fora de escopo (v2.0)

            - Edição colaborativa em tempo real.
            - Comentários e discussões no leitor.
            - Federação automática por webhook — nesta versão a sincronização é manual.
            MD;

        $v10 = <<<'MD'
            ## Problema

            A documentação da empresa vive espalhada em repositórios, drives e wikis. Ninguém sabe o que existe nem quem é o dono.

            ## Proposta

            Um catálogo **manual** de documentação: cada time cadastra suas entradas com título, resumo e link para onde o doc vive hoje.

            ## Métricas de sucesso

            - 50% dos docs ativos catalogados em 2 trimestres.

            ## Fora de escopo (v1.0)

            - Corpo do documento no próprio catálogo — apenas metadados e link.
            - Qualquer forma de sincronização com repositórios.
            MD;

        PrdVersion::query()->updateOrCreate(
            ['entry_id' => $entry->id, 'major' => 2, 'minor' => 1],
            ['body_markdown' => $v21, 'state' => PrdVersionState::Draft, 'frozen_at' => null],
        );

        PrdVersion::query()->updateOrCreate(
            ['entry_id' => $entry->id, 'major' => 2, 'minor' => 0],
            ['body_markdown' => $v20, 'state' => PrdVersionState::Frozen, 'frozen_at' => now()->subDays(7)],
        );

        PrdVersion::query()->updateOrCreate(
            ['entry_id' => $entry->id, 'major' => 1, 'minor' => 0],
            ['body_markdown' => $v10, 'state' => PrdVersionState::Frozen, 'frozen_at' => now()->subDays(54)],
        );
    }
}
