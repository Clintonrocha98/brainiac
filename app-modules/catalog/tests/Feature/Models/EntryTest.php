<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use Illuminate\Database\QueryException;

test('entry can be created with factory and casts enums', function (): void {
    $entry = Entry::factory()->create([
        'purpose' => Purpose::Reference,
        'format' => Format::Prd,
        'origin' => Origin::Native,
        'department' => Area::Ti,
        'status' => Status::Published,
    ]);

    expect($entry->purpose)->toBe(Purpose::Reference)
        ->and($entry->format)->toBe(Format::Prd)
        ->and($entry->department)->toBe(Area::Ti);
});

test('qualified_id is unique', function (): void {
    Entry::factory()->create(['qualified_id' => 'RPQ:PRD-1']);
    Entry::factory()->create(['qualified_id' => 'RPQ:PRD-1']);
})->throws(QueryException::class);

test('origin project is auto-synced into the projeto facet', function (): void {
    $project = Project::factory()->create();

    $entry = Entry::factory()->for($project, 'originProject')->create();

    expect($entry->projects()->pluck('catalog_projects.id'))
        ->toContain($project->id);
});

test('owner resolves to the identity user', function (): void {
    $user = User::factory()->create();
    $entry = Entry::factory()->for($user, 'owner')->create();

    expect($entry->owner->is($user))->toBeTrue();
});
