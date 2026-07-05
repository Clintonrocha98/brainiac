<?php

declare(strict_types=1);

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\DTOs\SnapshotEntry;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Federation\ReconcileSnapshot;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;

function snapshotEntry(string $qualifiedId, string $native): SnapshotEntry
{
    return new SnapshotEntry(
        qualifiedId: $qualifiedId,
        nativeId: $native,
        title: 'T',
        summary: 'S',
        purpose: Purpose::Reference,
        format: Format::Reference,
        department: Area::Ti,
        bodyMarkdown: '# body',
        gitPointer: sprintf('docs/%s.md', $native),
    );
}

test('reconcile upserts snapshot entries and deletes absent mirrors, sparing natives', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    // Espelho pré-existente que sumirá do snapshot.
    $stale = Entry::factory()->for($project, 'originProject')->create([
        'qualified_id' => 'RPQ:old', 'origin' => Origin::Mirror,
    ]);
    // Nativo do mesmo projeto — nunca pode ser tocado.
    $native = Entry::factory()->for($project, 'originProject')->create([
        'qualified_id' => 'RPQ:native', 'origin' => Origin::Native,
    ]);

    $snapshot = new Snapshot('RPQ', [
        snapshotEntry('RPQ:kept', 'kept'),   // novo
    ]);

    resolve(ReconcileSnapshot::class)->execute($snapshot);

    expect(Entry::query()->where('qualified_id', 'RPQ:kept')->exists())->toBeTrue()
        ->and(Entry::query()->whereKey($stale->id)->exists())->toBeFalse()   // espelho ausente removido
        ->and(Entry::query()->whereKey($native->id)->exists())->toBeTrue();  // nativo intacto
});

test('reconcile is idempotent', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);
    $snapshot = new Snapshot('RPQ', [snapshotEntry('RPQ:a', 'a'), snapshotEntry('RPQ:b', 'b')]);

    resolve(ReconcileSnapshot::class)->execute($snapshot);
    resolve(ReconcileSnapshot::class)->execute($snapshot);

    expect(Entry::query()->where('origin', Origin::Mirror)->count())->toBe(2);
});
