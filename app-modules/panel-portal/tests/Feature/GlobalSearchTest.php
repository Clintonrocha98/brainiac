<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use He4rt\Portal\Livewire\GlobalSearch;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('finds entries by title with a project hint', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);
    $entry = Entry::factory()->create([
        'project_id' => $project->id,
        'title' => 'Fila Unica Para Moderacao',
    ]);

    livewire(GlobalSearch::class)
        ->set('q', 'Fila Unica')
        ->assertSee('Fila Unica Para Moderacao')
        ->assertSee($entry->qualified_id)
        ->assertSee('RPQ');
});

test('finds entries by qualified id', function (): void {
    $entry = Entry::factory()->create(['qualified_id' => 'BRN:governanca/explanation/diataxis', 'native_id' => 'governanca/explanation/diataxis']);

    livewire(GlobalSearch::class)
        ->set('q', 'governanca/explanation')
        ->assertSee($entry->title);
});

test('shows an empty message when nothing matches', function (): void {
    livewire(GlobalSearch::class)
        ->set('q', 'nada-a-ver')
        ->assertSee('Nothing found');
});
