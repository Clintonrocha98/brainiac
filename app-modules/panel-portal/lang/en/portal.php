<?php

declare(strict_types=1);

return [
    'nav' => [
        'projects' => 'Projects',
        'areas' => 'Areas',
        'collections' => 'Trails',
    ],
    'topbar' => [
        'badge' => 'docs',
        'federation_synced' => 'federation · :time',
    ],
    'search' => [
        'placeholder' => 'Search across all documentation…',
        'empty' => 'Nothing found for ":query"',
    ],
    'index' => [
        'kicker' => 'Contexts',
        'project' => [
            'title' => 'Projects',
            'subtitle' => 'Origin repositories and products. Each project groups the documentation it owns or is about — federated ones are mirrored automatically on every push.',
            'meta' => ':count docs',
            'chip_federation' => 'federation',
            'chip_native' => 'native',
        ],
        'area' => [
            'title' => 'Areas',
            'subtitle' => 'Departments that own documentation. An area gathers its docs and the trails aimed at it.',
            'meta' => ':count docs',
            'chip_trails' => ':count trail|:count trails',
        ],
        'collection' => [
            'title' => 'Trails',
            'subtitle' => 'Ordered collections of documents, curated for a goal. A trail belongs to the areas of its audience.',
            'meta' => ':count docs in order',
        ],
    ],
    'context' => [
        'back' => [
            'project' => 'All projects',
            'area' => 'All areas',
            'collection' => 'All trails',
        ],
        'type' => [
            'project' => 'Project',
            'area' => 'Department',
            'collection' => 'Collection · trail',
        ],
        'overview' => 'Overview',
        'trail_group' => 'Trail',
        'mirror_hint' => 'Mirror — read-only',
    ],
    'overview' => [
        'docs_label' => 'Documentation · :count docs',
        'trail_label' => 'Trail · :count docs in order',
        'area_subtitle' => 'All the documentation owned by the :area department, from any project or origin.',
        'area_trails' => 'Trails for this area',
        'trail_meta' => ':count docs · trail',
        'branch' => 'branch :branch',
        'mirrored_via_federation' => 'mirrored via federation',
    ],
    'reader' => [
        'updated' => 'updated :date',
        'by' => 'by :owner',
        'mirror_banner' => 'Document mirrored from federation — read-only. The source of truth is the origin repository.',
        'no_body_title' => 'No document yet',
        'no_body_text' => 'This entry exists in the catalog, but the document body has not been created.',
        'links' => 'Links',
        'artifacts' => 'Artifacts',
        'previous' => '← Previous',
        'next' => 'Next →',
        'on_this_page' => 'On this page',
        'about' => 'About this doc',
        'department' => 'Dept.',
        'audience' => 'Audience',
        'owner' => 'Owner',
        'authors' => 'Authors',
        'status' => 'Status',
        'view_source' => 'View source',
    ],
    'markdown' => [
        'mermaid_label' => 'Diagram · Mermaid',
        'image_label' => 'Image · :alt',
    ],
    'prd' => [
        'versions' => 'PRD versions',
        'editing' => 'in progress',
        'old_version_banner' => 'You are reading version :version, frozen on :date.',
        'view_latest' => 'View :version →',
    ],
    'links' => [
        'supersedes' => ['out' => 'Supersedes', 'in' => 'Superseded by'],
        'related' => ['out' => 'Related', 'in' => 'Related'],
        'depends_on' => ['out' => 'Depends on', 'in' => 'Dependency of'],
        'part_of' => ['out' => 'Part of', 'in' => 'Contains'],
    ],
    'areas_desc' => [
        'ti' => 'Platform, services, architecture and technical decisions.',
        'business' => 'Processes, governance and business knowledge.',
        'product' => 'PRDs, product decisions and flows.',
        'marketing' => 'Communication, adoption and launch plans.',
        'design' => 'Tokens, patterns and visual references.',
    ],
];
