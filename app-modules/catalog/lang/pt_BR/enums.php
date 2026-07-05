<?php

declare(strict_types=1);

return [
    'purpose' => ['reference' => 'Referência', 'how-to' => 'How-to', 'explanation' => 'Explicação'],
    'format' => [
        'readme' => 'README', 'context' => 'CONTEXT', 'architecture' => 'Arquitetura', 'reference' => 'Referência',
        'how-to' => 'How-to', 'explanation' => 'Explicação', 'adr' => 'ADR',
        'spec' => 'Spec', 'plan' => 'Plano', 'prd' => 'PRD',
    ],
    'origin' => ['native' => 'Nativo', 'mirror' => 'Espelho'],
    'area' => ['ti' => 'TI', 'business' => 'Negócio', 'product' => 'Produto', 'marketing' => 'Marketing', 'design' => 'Design'],
    'audience' => [
        'ti' => 'TI', 'business' => 'Negócio', 'product' => 'Produto', 'marketing' => 'Marketing',
        'design' => 'Design', 'all' => 'Todos', 'external' => 'Externo',
    ],
    'status' => ['draft' => 'Rascunho', 'review' => 'Revisão', 'published' => 'Publicado', 'obsolete' => 'Obsoleto'],
    'entry_link_type' => ['supersedes' => 'Substitui', 'related' => 'Relacionada', 'depends_on' => 'Depende de', 'part_of' => 'Parte de'],
    'prd_version_state' => ['draft' => 'Rascunho', 'frozen' => 'Congelada'],
];
