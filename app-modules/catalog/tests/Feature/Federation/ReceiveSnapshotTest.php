<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;

/**
 * @return array{0: array<string, mixed>, 1: string}
 */
function signedPayload(Project $project): array
{
    $body = [
        'acronym' => $project->acronym,
        'entries' => [[
            'qualified_id' => $project->acronym.':kept', 'native_id' => 'kept',
            'title' => 'T', 'summary' => 'S', 'purpose' => 'reference',
            'format' => 'reference', 'department' => 'ti',
            'body_markdown' => '# body', 'git_pointer' => 'docs/kept.md',
        ]],
    ];
    $json = json_encode($body);
    $signature = hash_hmac('sha256', $json, 'top-secret');

    return [$body, $signature];
}

test('accepts a correctly signed snapshot and reconciles', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    [$body, $signature] = signedPayload($project);

    $this->withHeader('X-Signature', $signature)
        ->postJson('/federation/snapshot', $body)
        ->assertOk();

    expect(Entry::query()->where('qualified_id', 'RPQ:kept')->exists())->toBeTrue();
});

test('rejects a wrong signature', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    [$body] = signedPayload($project);

    $this->withHeader('X-Signature', 'wrong')
        ->postJson('/federation/snapshot', $body)
        ->assertForbidden();
});
