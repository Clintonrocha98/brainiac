<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
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
            'audience' => ['ti', 'product'],
            'keywords' => ['fila', 'evento'],
            'status' => 'draft',
            'projects' => [],
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
        ->postJson('/webhook/snapshot', $body)
        ->assertOk();

    $entry = Entry::query()->where('qualified_id', 'RPQ:kept')->firstOrFail();
    $project->refresh();

    expect($entry->authors)->toContain('Clintonrocha98')
        ->and($entry->status)->toBe(Status::Draft)
        ->and($entry->audience->all())->toBe([Audience::Ti, Audience::Product])
        ->and($entry->keywords)->toBe(['fila', 'evento'])
        ->and($project->repo_url)->toBe('https://github.com/3pontos-tech/rpq');
});

test('rejects a wrong signature', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    [$body] = signedPayload($project);

    $this->withHeader('X-Signature', 'wrong')
        ->postJson('/webhook/snapshot', $body)
        ->assertForbidden();
});

test('applies contract defaults when optional facets are absent from the payload', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    $body = [
        'acronym' => 'RPQ',
        'entries' => [[
            'qualified_id' => 'RPQ:min', 'native_id' => 'min',
            'title' => 'T', 'summary' => 'S', 'purpose' => 'reference',
            'format' => 'reference', 'department' => 'product',
            'body_markdown' => '# body',
        ]],
    ];
    $signature = hash_hmac('sha256', (string) json_encode($body), 'top-secret');

    $this->withHeader('X-Signature', $signature)
        ->postJson('/webhook/snapshot', $body)
        ->assertOk();

    $entry = Entry::query()->where('qualified_id', 'RPQ:min')->firstOrFail();

    expect($entry->status)->toBe(Status::Published)          // default do contrato
        ->and($entry->audience->all())->toBe([Audience::Product]); // default → [department]
});
