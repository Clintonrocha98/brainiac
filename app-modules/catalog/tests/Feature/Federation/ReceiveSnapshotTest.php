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
        'repo_url' => 'https://github.com/3pontos-tech/rpq',
        'default_branch' => 'develop',
        'entries' => [[
            'qualified_id' => $project->acronym.':kept', 'native_id' => 'kept',
            'title' => 'T', 'summary' => 'S', 'purpose' => 'reference',
            'format' => 'reference', 'department' => 'ti',
            'body_markdown' => '# body', 'git_pointer' => 'docs/kept.md',
            'authors' => ['Clintonrocha98'],
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

    $entry = Entry::query()->where('qualified_id', 'RPQ:kept')->firstOrFail();
    $project->refresh();

    expect($entry->authors)->toContain('Clintonrocha98')
        ->and($project->repo_url)->toBe('https://github.com/3pontos-tech/rpq');
});

test('rejects a wrong signature', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    [$body] = signedPayload($project);

    $this->withHeader('X-Signature', 'wrong')
        ->postJson('/federation/snapshot', $body)
        ->assertForbidden();
});
