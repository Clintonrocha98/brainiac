<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use He4rt\Portal\Filament\Search\EntryGlobalSearchProvider;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    Filament::setCurrentPanel('portal');
    $this->actingAs(User::factory()->create());
});

test('finds entries by title with a project hint', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);
    $entry = Entry::factory()->create(['title' => 'Fila Unica Para Moderacao']);
    $entry->projects()->attach($project);

    $results = (new EntryGlobalSearchProvider)->getResults('Fila Unica');

    /** @var Collection<int, GlobalSearchResult> $documents */
    $documents = $results?->getCategories()->first();

    expect($documents)->toHaveCount(1)
        ->and($documents->first()->title)->toBe('Fila Unica Para Moderacao')
        ->and($documents->first()->details)->toContain($entry->qualified_id)
        ->and($documents->first()->details)->toContain('RPQ')
        ->and($documents->first()->url)->toContain('/portal/projects/'.$project->slug.'/e/'.$entry->id);
});

test('finds entries by qualified id and falls back to the area url', function (): void {
    $entry = Entry::factory()->create([
        'qualified_id' => 'BRN:governanca/explanation/diataxis',
        'native_id' => 'governanca/explanation/diataxis',
    ]);

    $results = (new EntryGlobalSearchProvider)->getResults('governanca/explanation');

    /** @var Collection<int, GlobalSearchResult> $documents */
    $documents = $results?->getCategories()->first();

    expect($documents)->toHaveCount(1)
        ->and($documents->first()->title)->toBe($entry->title)
        ->and($documents->first()->url)->toContain('/portal/areas/'.$entry->department->value.'/e/'.$entry->id);
});

test('returns an empty result set when nothing matches', function (): void {
    Entry::factory()->create(['title' => 'Outro Assunto']);

    $results = (new EntryGlobalSearchProvider)->getResults('nada-a-ver');

    expect($results?->getCategories()->first())->toBeEmpty();
});

test('blank query returns null', function (): void {
    expect((new EntryGlobalSearchProvider)->getResults('   '))->toBeNull();
});
