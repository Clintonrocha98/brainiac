<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;

test('an artifact-only entry points to a url', function (): void {
    $entry = Entry::factory()->create();

    $artifact = EntryArtifact::factory()->for($entry)->create([
        'url' => 'https://waifuvault.moe/f/x.html',
    ]);

    expect($entry->artifacts()->count())->toBe(1)
        ->and($artifact->url)->toEndWith('.html');
});
