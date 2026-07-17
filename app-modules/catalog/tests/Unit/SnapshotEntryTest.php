<?php

declare(strict_types=1);

use He4rt\Catalog\DTOs\SnapshotEntry;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function snapshotPayloadEntry(array $overrides = []): array
{
    return array_merge([
        'qualified_id' => 'RPQ:doc',
        'native_id' => 'doc',
        'title' => 'Título',
        'summary' => 'Resumo',
        'purpose' => 'reference',
        'format' => 'reference',
        'department' => 'ti',
        'body_markdown' => '# corpo',
    ], $overrides);
}

test('fromPayload applies the contract defaults for absent optional fields', function (): void {
    $entry = SnapshotEntry::fromPayload(snapshotPayloadEntry());

    expect($entry->audience)->toBe([Audience::Ti])       // default → [department]
        ->and($entry->status)->toBe(Status::Published)   // default → published
        ->and($entry->keywords)->toBeEmpty()
        ->and($entry->projectAcronyms)->toBeEmpty()
        ->and($entry->authors)->toBeEmpty()
        ->and($entry->gitPointer)->toBeNull();
});

test('fromPayload reads the optional facets from the payload', function (): void {
    $entry = SnapshotEntry::fromPayload(snapshotPayloadEntry([
        'audience' => ['ti', 'product'],
        'keywords' => ['fila', 'evento'],
        'status' => 'draft',
        'projects' => ['ECO', 'GAM'],
        'authors' => ['Clintonrocha98'],
        'git_pointer' => 'docs/doc.md',
    ]));

    expect($entry->audience)->toBe([Audience::Ti, Audience::Product])
        ->and($entry->keywords)->toBe(['fila', 'evento'])
        ->and($entry->status)->toBe(Status::Draft)
        ->and($entry->projectAcronyms)->toBe(['ECO', 'GAM'])
        ->and($entry->authors)->toBe(['Clintonrocha98'])
        ->and($entry->gitPointer)->toBe('docs/doc.md');
});

test('fromPayload falls back to the department audience when the list is empty', function (): void {
    $entry = SnapshotEntry::fromPayload(snapshotPayloadEntry([
        'department' => 'product',
        'audience' => ['', ''], // só entradas vazias → filtradas → cai no default
    ]));

    expect($entry->audience)->toBe([Audience::Product]);
});
