<?php

declare(strict_types=1);

use He4rt\Catalog\Actions\CreateNativeEntry;
use He4rt\Catalog\Actions\FreezePrdVersion;
use He4rt\Catalog\Actions\RotateFederationCredentials;
use He4rt\Catalog\Actions\UpdateNativeEntry;
use He4rt\Catalog\DTOs\NativeEntryData;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;

/**
 * @param  array<string, mixed>  $overrides
 */
function nativeEntryData(User $owner, array $overrides = []): NativeEntryData
{
    return NativeEntryData::fromForm([
        'native_id' => 'onboarding/how-to/publicar-doc',
        'title' => 'Publicar Um Documento',
        'summary' => 'Guia de publicação.',
        'purpose' => Purpose::HowTo->value,
        'format' => Format::HowTo->value,
        'department' => Area::Product->value,
        'audience' => [Audience::All->value],
        'keywords' => ['fluxo'],
        'status' => Status::Draft->value,
        'owner_id' => $owner->id,
        ...$overrides,
    ]);
}

test('creates a native entry minting the qualified id from the department', function (): void {
    $owner = User::factory()->create();
    $subjectProject = Project::factory()->create();

    $entry = resolve(CreateNativeEntry::class)->execute(nativeEntryData($owner, [
        'subject_project_ids' => [$subjectProject->id],
        'body_markdown' => '## Passos',
    ]));

    expect($entry->qualified_id)->toBe('PRODUCT:onboarding/how-to/publicar-doc')
        ->and($entry->origin)->toBe(Origin::Native)
        ->and($entry->owner_id)->toBe($owner->id)
        ->and($entry->document?->body_markdown)->toBe('## Passos')
        ->and($entry->projects->pluck('id')->all())->toContain($subjectProject->id);
});

test('blank body does not create a document', function (): void {
    $owner = User::factory()->create();

    $entry = resolve(CreateNativeEntry::class)->execute(nativeEntryData($owner, [
        'body_markdown' => '   ',
    ]));

    expect($entry->document)->toBeNull();
});

test('updating re-mints the qualified id when the department changes', function (): void {
    $owner = User::factory()->create();
    $entry = resolve(CreateNativeEntry::class)->execute(nativeEntryData($owner, [
        'body_markdown' => '## Passos',
    ]));

    $updated = resolve(UpdateNativeEntry::class)->execute($entry, nativeEntryData($owner, [
        'department' => Area::Business->value,
        'body_markdown' => '## Passos revisados',
    ]));

    expect($updated->qualified_id)->toBe('BUSINESS:onboarding/how-to/publicar-doc')
        ->and($updated->department)->toBe(Area::Business)
        ->and($updated->document?->body_markdown)->toBe('## Passos revisados');
});

test('updating with a blank body removes the document', function (): void {
    $owner = User::factory()->create();
    $entry = resolve(CreateNativeEntry::class)->execute(nativeEntryData($owner, [
        'body_markdown' => '## Passos',
    ]));

    $updated = resolve(UpdateNativeEntry::class)->execute($entry, nativeEntryData($owner, [
        'body_markdown' => null,
    ]));

    expect($updated->document)->toBeNull();
});

test('rotating federation credentials persists new values and reveals them once', function (): void {
    $project = Project::factory()->create();

    $credentials = resolve(RotateFederationCredentials::class)->execute($project);

    $project->refresh();

    expect($credentials->webhookToken)->toHaveLength(48)
        ->and($credentials->hmacSecret)->toHaveLength(64)
        ->and($project->webhook_token)->toBe($credentials->webhookToken)
        ->and($project->hmac_secret)->toBe($credentials->hmacSecret);
});

test('freezing a prd version sets the state and freeze date', function (): void {
    $version = PrdVersion::factory()->create(['major' => 2, 'minor' => 0]);

    resolve(FreezePrdVersion::class)->execute($version);

    $version->refresh();

    expect($version->state)->toBe(PrdVersionState::Frozen)
        ->and($version->frozen_at)->not->toBeNull();
});

test('native entry search scope matches title, summary, id and keywords', function (): void {
    Entry::factory()->create(['title' => 'Fila Unica Para Moderacao', 'keywords' => ['sqs', 'prioridade']]);
    Entry::factory()->create(['title' => 'Outro Assunto', 'keywords' => ['tokens']]);

    expect(Entry::query()->searching('Fila Unica')->count())->toBe(1)
        ->and(Entry::query()->searching('prioridade')->count())->toBe(1)
        ->and(Entry::query()->searching('inexistente')->count())->toBe(0);
});
