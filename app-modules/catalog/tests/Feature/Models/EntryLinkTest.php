<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;

test('a link connects two existing entries with a type', function (): void {
    $from = Entry::factory()->create();
    $to = Entry::factory()->create();

    $link = EntryLink::factory()->create([
        'from_entry_id' => $from->id,
        'to_entry_id' => $to->id,
        'type' => EntryLinkType::Supersedes,
    ]);

    expect($link->fromEntry->is($from))->toBeTrue()
        ->and($link->toEntry->is($to))->toBeTrue()
        ->and($link->type)->toBe(EntryLinkType::Supersedes);
});
