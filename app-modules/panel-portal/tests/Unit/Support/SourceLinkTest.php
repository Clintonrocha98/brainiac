<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\SourceLink;
use Illuminate\Support\Str;

/**
 * Monta uma Entry em memória (sem tocar o banco) com as partes do backlink.
 */
function entryForSourceLink(
    Origin $origin,
    ?string $repoUrl,
    ?string $defaultBranch,
    ?string $gitPointer,
): Entry {
    $entry = Entry::factory()->make(['origin' => $origin, 'owner_id' => null]);

    $entry->setRelation('originProject', Project::factory()->make([
        'repo_url' => $repoUrl,
        'default_branch' => $defaultBranch,
    ]));

    $entry->setRelation('document', Document::factory()->make([
        'entry_id' => (string) Str::uuid(),
        'git_pointer' => $gitPointer,
    ]));

    return $entry;
}

test('composes the source url for a complete mirror', function (): void {
    $entry = entryForSourceLink(Origin::Mirror, 'https://github.com/3pontos/rank-query', 'develop', 'docs/adr/0003.md');

    expect(SourceLink::for($entry))->toBe('https://github.com/3pontos/rank-query/blob/develop/docs/adr/0003.md');
});

test('returns null for a native entry', function (): void {
    $entry = entryForSourceLink(Origin::Native, 'https://github.com/3pontos/rank-query', 'main', 'docs/adr/0003.md');

    expect(SourceLink::for($entry))->toBeNull();
});

test('returns null when the mirror has no git pointer', function (): void {
    $entry = entryForSourceLink(Origin::Mirror, 'https://github.com/3pontos/rank-query', 'main', gitPointer: null);

    expect(SourceLink::for($entry))->toBeNull();
});

test('returns null when the origin project has no repo url', function (): void {
    $entry = entryForSourceLink(Origin::Mirror, repoUrl: null, defaultBranch: 'main', gitPointer: 'docs/adr/0003.md');

    expect(SourceLink::for($entry))->toBeNull();
});

test('falls back to main when the default branch is unknown', function (): void {
    $entry = entryForSourceLink(Origin::Mirror, 'https://github.com/3pontos/rank-query', defaultBranch: null, gitPointer: 'docs/adr/0003.md');

    expect(SourceLink::for($entry))->toBe('https://github.com/3pontos/rank-query/blob/main/docs/adr/0003.md');
});

test('normalizes duplicated slashes between the parts', function (): void {
    $entry = entryForSourceLink(Origin::Mirror, 'https://github.com/3pontos/rank-query/', 'main', '/docs/adr/0003.md');

    expect(SourceLink::for($entry))->toBe('https://github.com/3pontos/rank-query/blob/main/docs/adr/0003.md');
});
