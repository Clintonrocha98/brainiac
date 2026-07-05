<?php

declare(strict_types=1);

use He4rt\Catalog\Actions\MintQualifiedId;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Project;

test('prefixes with the origin project acronym', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    expect(app(MintQualifiedId::class)->execute($project, Area::Ti, 'PRD-12'))
        ->toBe('RPQ:PRD-12');
});

test('falls back to the department area when there is no origin', function (): void {
    expect(app(MintQualifiedId::class)->execute(null, Area::Design, 'how-to/handoff'))
        ->toBe('DESIGN:how-to/handoff');
});
