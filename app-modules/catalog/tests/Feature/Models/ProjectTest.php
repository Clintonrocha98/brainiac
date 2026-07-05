<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Project;
use Illuminate\Database\QueryException;

test('project can be created with factory', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    expect($project->acronym)->toBe('RPQ')
        ->and($project->id)->toBeString();
});

test('acronym is unique', function (): void {
    Project::factory()->create(['acronym' => 'RPQ']);
    Project::factory()->create(['acronym' => 'RPQ']);
})->throws(QueryException::class);
