<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;

test('document belongs to an entry and derives body facts on save', function (): void {
    $entry = Entry::factory()->create();

    $document = Document::factory()->for($entry)->create([
        'body_markdown' => "![x](a.png)\n```mermaid\ngraph TD;A-->B;\n```",
    ]);

    expect($document->entry->is($entry))->toBeTrue()
        ->and($document->has_image)->toBeTrue()
        ->and($document->has_mermaid)->toBeTrue();
});

test('mirror document keeps a git pointer', function (): void {
    $document = Document::factory()->create(['git_pointer' => 'docs/x.md']);

    expect($document->git_pointer)->toBe('docs/x.md');
});
