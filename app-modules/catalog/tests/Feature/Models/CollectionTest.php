<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Entry;

test('a collection carries a body and an ordered list of existing entries', function (): void {
    $first = Entry::factory()->create();
    $second = Entry::factory()->create();

    $collection = Collection::factory()->create([
        'body_markdown' => 'Bem-vindo! Comece por [pagamentos](RPQ:pagamentos/reference/x).',
    ]);
    $collection->entries()->attach([
        $first->id => ['position' => 1],
        $second->id => ['position' => 2],
    ]);

    expect($collection->entries()->orderByPivot('position')->pluck('catalog_entries.id')->all())
        ->toBe([$first->id, $second->id])
        ->and($collection->mentions)->toContain('RPQ:pagamentos/reference/x');
});
