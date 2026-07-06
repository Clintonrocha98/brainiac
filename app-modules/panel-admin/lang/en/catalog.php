<?php

declare(strict_types=1);

return [
    'entries' => [
        'singular' => 'Entry',
        'plural' => 'Entries',
        'sections' => [
            'identification' => 'Identification',
            'classification' => 'Classification',
            'content' => 'Content',
        ],
        'fields' => [
            'native_id' => 'Native id',
            'native_id_helper' => 'Free path inside the department (e.g. "onboarding/how-to/publish-doc"). The qualified id is minted as DEPARTMENT:native_id.',
            'qualified_id' => 'Qualified id',
            'title' => 'Title',
            'summary' => 'Summary',
            'purpose' => 'Purpose',
            'format' => 'Format',
            'department' => 'Department',
            'audience' => 'Audience',
            'keywords' => 'Keywords',
            'status' => 'Status',
            'owner' => 'Owner',
            'origin' => 'Origin',
            'subject_projects' => 'Subject projects',
            'body' => 'Document body (Markdown)',
            'updated_at' => 'Updated at',
        ],
    ],
    'prd_versions' => [
        'title' => 'PRD versions',
        'singular' => 'PRD version',
        'plural' => 'PRD versions',
        'fields' => [
            'version' => 'Version',
            'major' => 'Major',
            'minor' => 'Minor',
            'state' => 'State',
            'frozen_at' => 'Frozen at',
            'body' => 'Body (Markdown)',
        ],
        'actions' => [
            'freeze' => [
                'label' => 'Freeze',
                'heading' => 'Freeze this version?',
                'description' => 'A frozen version becomes immutable and is identified by its freeze date in the reader.',
                'success' => 'Version frozen.',
            ],
        ],
    ],
    'projects' => [
        'singular' => 'Project',
        'plural' => 'Projects',
        'sections' => [
            'identification' => 'Identification',
            'federation' => 'Federation',
        ],
        'fields' => [
            'business_name' => 'Business name',
            'technical_name' => 'Technical name',
            'slug' => 'Slug',
            'acronym' => 'Acronym',
            'acronym_helper' => 'Prefix used to mint qualified ids of mirrored entries (e.g. "PAY").',
            'repo_url' => 'Repository URL',
            'default_branch' => 'Default branch',
            'last_synced_at' => 'Last synced at',
            'never_synced' => 'Never synced',
            'federation' => 'Federation',
            'federation_configured' => 'configured',
            'federation_pending' => 'pending',
        ],
        'actions' => [
            'rotate' => [
                'label' => 'Rotate federation credentials',
                'heading' => 'Rotate federation credentials?',
                'description' => 'The current webhook token and HMAC secret stop working immediately. The origin repository must be reconfigured with the new values.',
                'success_title' => 'Credentials rotated',
                'success_body' => 'Copy the values now — they will not be shown again.',
                'token_label' => 'Webhook token',
                'secret_label' => 'HMAC secret',
            ],
        ],
    ],
    'collections' => [
        'singular' => 'Trail',
        'plural' => 'Trails',
        'fields' => [
            'title' => 'Title',
            'slug' => 'Slug',
            'summary' => 'Summary',
            'audience' => 'Audience',
            'owner' => 'Owner',
            'status' => 'Status',
            'body' => 'Introduction (Markdown)',
            'entries_count' => 'Docs',
        ],
        'entries_relation' => [
            'title' => 'Trail (ordered docs)',
            'fields' => [
                'position' => 'Position',
            ],
        ],
    ],
];
