<?php

declare(strict_types=1);

return [
    'nav' => [
        'projects' => 'Projetos',
        'areas' => 'Áreas',
        'collections' => 'Trilhas',
    ],
    'topbar' => [
        'badge' => 'docs',
        'federation_synced' => 'federação · :time',
    ],
    'search' => [
        'placeholder' => 'Buscar em toda a documentação…',
        'empty' => 'Nada encontrado para ":query"',
    ],
    'index' => [
        'kicker' => 'Contextos',
        'project' => [
            'title' => 'Projetos',
            'subtitle' => 'Repositórios e produtos de origem. Cada projeto agrupa a documentação de que é dono ou assunto — os federados são espelhados automaticamente a cada push.',
            'meta' => ':count docs',
            'chip_federation' => 'federação',
            'chip_native' => 'nativo',
        ],
        'area' => [
            'title' => 'Áreas',
            'subtitle' => 'Departamentos donos da documentação. Uma área reúne seus docs e as trilhas destinadas a ela.',
            'meta' => ':count docs',
            'chip_trails' => ':count trilha|:count trilhas',
        ],
        'collection' => [
            'title' => 'Trilhas',
            'subtitle' => 'Coleções ordenadas de documentos, curadas para um objetivo. Uma trilha pertence às áreas do seu público.',
            'meta' => ':count docs em ordem',
        ],
    ],
    'context' => [
        'back' => [
            'project' => 'Todos os projetos',
            'area' => 'Todas as áreas',
            'collection' => 'Todas as trilhas',
        ],
        'type' => [
            'project' => 'Projeto',
            'area' => 'Departamento',
            'collection' => 'Coleção · trilha',
        ],
        'overview' => 'Visão geral',
        'trail_group' => 'Trilha',
        'mirror_hint' => 'Espelho — somente leitura',
    ],
    'overview' => [
        'docs_label' => 'Documentação · :count docs',
        'trail_label' => 'Trilha · :count docs em ordem',
        'area_subtitle' => 'Toda a documentação de que o departamento de :area é dono, de qualquer projeto ou origem.',
        'area_trails' => 'Trilhas desta área',
        'trail_meta' => ':count docs · trilha',
        'branch' => 'branch :branch',
        'mirrored_via_federation' => 'espelhado via federação',
    ],
    'reader' => [
        'updated' => 'atualizado :date',
        'by' => 'por :owner',
        'mirror_banner' => 'Documento espelhado da federação — somente leitura. A fonte da verdade é o repositório de origem.',
        'no_body_title' => 'Sem documento ainda',
        'no_body_text' => 'Esta entrada existe no catálogo, mas o corpo do documento não foi criado.',
        'links' => 'Ligações',
        'artifacts' => 'Artefatos',
        'previous' => '← Anterior',
        'next' => 'Próximo →',
        'on_this_page' => 'Nesta página',
        'about' => 'Sobre este doc',
        'department' => 'Depto.',
        'audience' => 'Público',
        'owner' => 'Dono',
        'authors' => 'Autores',
        'status' => 'Status',
        'view_source' => 'Ver na fonte',
    ],
    'markdown' => [
        'mermaid_label' => 'Diagrama · Mermaid',
        'image_label' => 'Imagem · :alt',
    ],
    'prd' => [
        'versions' => 'Versões do PRD',
        'editing' => 'em edição',
        'old_version_banner' => 'Você está lendo a versão :version, congelada em :date.',
        'view_latest' => 'Ver :version →',
    ],
    'links' => [
        'supersedes' => ['out' => 'Substitui', 'in' => 'Substituída por'],
        'related' => ['out' => 'Relacionada', 'in' => 'Relacionada'],
        'depends_on' => ['out' => 'Depende de', 'in' => 'Dependência de'],
        'part_of' => ['out' => 'Parte de', 'in' => 'Contém'],
    ],
    'areas_desc' => [
        'ti' => 'Plataforma, serviços, arquitetura e decisões técnicas.',
        'business' => 'Processos, governança e conhecimento de negócio.',
        'product' => 'PRDs, decisões de produto e fluxos.',
        'marketing' => 'Planos de comunicação, adoção e lançamento.',
        'design' => 'Tokens, padrões e referências visuais.',
    ],
];
