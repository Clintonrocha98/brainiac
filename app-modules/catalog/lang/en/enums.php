<?php

declare(strict_types=1);

return [
    'purpose' => ['reference' => 'Reference', 'how-to' => 'How-to', 'explanation' => 'Explanation'],
    'format' => [
        'readme' => 'README', 'context' => 'CONTEXT', 'architecture' => 'Architecture', 'reference' => 'Reference',
        'how-to' => 'How-to', 'explanation' => 'Explanation', 'adr' => 'ADR',
        'spec' => 'Spec', 'plan' => 'Plan', 'prd' => 'PRD',
    ],
    'origin' => ['native' => 'Native', 'mirror' => 'Mirror'],
    'area' => ['ti' => 'IT', 'business' => 'Business', 'product' => 'Product', 'marketing' => 'Marketing', 'design' => 'Design'],
    'audience' => [
        'ti' => 'IT', 'business' => 'Business', 'product' => 'Product', 'marketing' => 'Marketing',
        'design' => 'Design', 'all' => 'All', 'external' => 'External',
    ],
    'status' => ['draft' => 'Draft', 'review' => 'Review', 'published' => 'Published', 'obsolete' => 'Obsolete'],
    'entry_link_type' => ['supersedes' => 'Supersedes', 'related' => 'Related', 'depends_on' => 'Depends on', 'part_of' => 'Part of'],
    'prd_version_state' => ['draft' => 'Draft', 'frozen' => 'Frozen'],
];
