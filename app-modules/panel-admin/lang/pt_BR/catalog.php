<?php

declare(strict_types=1);

return [
    'entries' => [
        'singular' => 'Entrada',
        'plural' => 'Entradas',
        'sections' => [
            'identification' => 'Identificação',
            'classification' => 'Classificação',
            'content' => 'Conteúdo',
        ],
        'fields' => [
            'native_id' => 'Id nativo',
            'native_id_helper' => 'Caminho livre dentro do departamento (ex.: "onboarding/how-to/publicar-doc"). O id qualificado é cunhado como DEPARTAMENTO:id_nativo.',
            'qualified_id' => 'Id qualificado',
            'title' => 'Título',
            'summary' => 'Resumo',
            'purpose' => 'Propósito',
            'format' => 'Formato',
            'department' => 'Departamento',
            'audience' => 'Público',
            'keywords' => 'Palavras-chave',
            'status' => 'Status',
            'owner' => 'Dono',
            'origin' => 'Origem',
            'subject_projects' => 'Projetos (assunto)',
            'body' => 'Corpo do documento (Markdown)',
            'updated_at' => 'Atualizada em',
        ],
    ],
    'prd_versions' => [
        'title' => 'Versões do PRD',
        'singular' => 'Versão do PRD',
        'plural' => 'Versões do PRD',
        'fields' => [
            'version' => 'Versão',
            'major' => 'Major',
            'minor' => 'Minor',
            'state' => 'Estado',
            'frozen_at' => 'Congelada em',
            'body' => 'Corpo (Markdown)',
        ],
        'actions' => [
            'freeze' => [
                'label' => 'Congelar',
                'heading' => 'Congelar esta versão?',
                'description' => 'Uma versão congelada torna-se imutável e passa a ser identificada pela data de congelamento no leitor.',
                'success' => 'Versão congelada.',
            ],
        ],
    ],
    'projects' => [
        'singular' => 'Projeto',
        'plural' => 'Projetos',
        'sections' => [
            'identification' => 'Identificação',
            'federation' => 'Federação',
        ],
        'fields' => [
            'business_name' => 'Nome de negócio',
            'technical_name' => 'Nome técnico',
            'slug' => 'Slug',
            'acronym' => 'Sigla',
            'acronym_helper' => 'Prefixo usado para cunhar os ids qualificados das entradas espelhadas (ex.: "PAY").',
            'repo_url' => 'URL do repositório',
            'default_branch' => 'Branch padrão',
            'last_synced_at' => 'Última sincronização',
            'never_synced' => 'Nunca sincronizado',
            'federation' => 'Federação',
            'federation_configured' => 'configurada',
            'federation_pending' => 'pendente',
        ],
        'actions' => [
            'rotate' => [
                'label' => 'Rotacionar credenciais da federação',
                'heading' => 'Rotacionar as credenciais da federação?',
                'description' => 'O token do webhook e o segredo HMAC atuais param de funcionar imediatamente. O repositório de origem precisa ser reconfigurado com os novos valores.',
                'success_title' => 'Credenciais rotacionadas',
                'success_body' => 'Copie os valores agora — eles não serão exibidos novamente.',
                'token_label' => 'Token do webhook',
                'secret_label' => 'Segredo HMAC',
            ],
        ],
    ],
    'collections' => [
        'singular' => 'Trilha',
        'plural' => 'Trilhas',
        'fields' => [
            'title' => 'Título',
            'slug' => 'Slug',
            'summary' => 'Resumo',
            'audience' => 'Público',
            'owner' => 'Dono',
            'status' => 'Status',
            'body' => 'Introdução (Markdown)',
            'entries_count' => 'Docs',
        ],
        'entries_relation' => [
            'title' => 'Trilha (docs em ordem)',
            'fields' => [
                'position' => 'Posição',
            ],
        ],
    ],
];
