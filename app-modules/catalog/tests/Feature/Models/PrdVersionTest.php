<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;

test('a prd entry can hold several versions', function (): void {
    $entry = Entry::factory()->prd()->create();

    PrdVersion::factory()->for($entry)->frozen()->create(['major' => 1, 'minor' => 0]);
    PrdVersion::factory()->for($entry)->create(); // draft

    expect($entry->prdVersions()->count())->toBe(2);
});

test('a frozen version records its state and timestamp', function (): void {
    $version = PrdVersion::factory()->frozen()->create();

    expect($version->state)->toBe(PrdVersionState::Frozen)
        ->and($version->frozen_at)->not->toBeNull();
});
