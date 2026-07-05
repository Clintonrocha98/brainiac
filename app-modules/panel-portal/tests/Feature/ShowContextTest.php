<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;

beforeEach(function (): void {
    $this->withoutVite();
    $this->actingAs(User::factory()->create());
});

test('project overview shows the repo bar and docs grouped by purpose', function (): void {
    $project = Project::factory()->create([
        'repo_url' => 'https://github.com/3pontos/rank-query',
        'default_branch' => 'main',
        'last_synced_at' => now(),
    ]);
    $entry = Entry::factory()->create([
        'project_id' => $project->id,
        'origin' => Origin::Mirror,
        'owner_id' => null,
        'purpose' => Purpose::Explanation,
    ]);

    $this->get(route('portal.projects.show', ['project' => $project]))
        ->assertOk()
        ->assertSee('github.com/3pontos/rank-query')
        ->assertSee('branch main')
        ->assertSee('mirrored via federation')
        ->assertSee($entry->title)
        ->assertSee('Explanation');
});

test('area overview lists only entries owned by the department plus its trails', function (): void {
    $inArea = Entry::factory()->create(['department' => Area::Ti, 'title' => 'Mapa Da Plataforma']);
    $other = Entry::factory()->create(['department' => Area::Marketing, 'title' => 'Plano De Lancamento']);
    Collection::factory()->create(['title' => 'Onboarding De Devs', 'audience' => [Audience::All]]);

    $this->get(route('portal.areas.show', ['area' => 'ti']))
        ->assertOk()
        ->assertSee('Mapa Da Plataforma')
        ->assertDontSee('Plano De Lancamento')
        ->assertSee('Onboarding De Devs');
});

test('collection overview shows ordered positions and rendered intro prose', function (): void {
    $collection = Collection::factory()->create(['body_markdown' => 'Siga a trilha **em ordem**.']);
    [$first, $second] = Entry::factory()->count(2)->create();
    $collection->entries()->attach([$first->id => ['position' => 1], $second->id => ['position' => 2]]);

    $this->get(route('portal.collections.show', ['collection' => $collection]))
        ->assertOk()
        ->assertSee('01')
        ->assertSee('02')
        ->assertSee('<strong>em ordem</strong>', false)
        ->assertSee($first->title)
        ->assertSee($second->title);
});

test('unknown area returns 404', function (): void {
    $this->get('/portal/areas/finance')->assertNotFound();
});

test('unknown project slug returns 404', function (): void {
    $this->get('/portal/projects/nao-existe')->assertNotFound();
});
