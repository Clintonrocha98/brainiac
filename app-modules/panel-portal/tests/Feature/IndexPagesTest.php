<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;

beforeEach(function (): void {
    $this->withoutVite();
    $this->actingAs(User::factory()->create(['locale' => 'en']));
});

test('guest is redirected to the portal login', function (): void {
    auth()->logout();

    $this->get('/portal/projects')->assertRedirect();
});

test('portal home redirects to the projects index', function (): void {
    $this->get('/portal')->assertRedirectContains('/portal/projects');
});

test('projects index lists projects with doc count and federation chip', function (): void {
    $federated = Project::factory()->create(['business_name' => 'Rank Query', 'acronym' => 'RPQ', 'last_synced_at' => now()]);
    Project::factory()->create(['business_name' => 'Brainiac Docs', 'acronym' => 'BRN']);

    Entry::factory()->create(['project_id' => $federated->id, 'origin' => Origin::Mirror, 'owner_id' => null]);

    $this->get('/portal/projects')
        ->assertOk()
        ->assertSee('Rank Query')
        ->assertSee('RPQ')
        ->assertSee('1 docs')
        ->assertSee('federation')
        ->assertSee('Brainiac Docs')
        ->assertSee('native');
});

test('areas index shows every area with its doc count', function (): void {
    Entry::factory()->count(2)->create(['department' => Area::Ti]);

    $this->get('/portal/areas')
        ->assertOk()
        ->assertSee('IT')
        ->assertSee('Business')
        ->assertSee('Product')
        ->assertSee('Marketing')
        ->assertSee('Design')
        ->assertSee('2 docs');
});

test('collections index lists trails with ordered doc count and audience chips', function (): void {
    $collection = Collection::factory()->create(['title' => 'Dev Onboarding', 'audience' => [Audience::Ti]]);
    [$first, $second] = Entry::factory()->count(2)->create();
    $collection->entries()->attach([$first->id => ['position' => 1], $second->id => ['position' => 2]]);

    $this->get('/portal/collections')
        ->assertOk()
        ->assertSee('Dev Onboarding')
        ->assertSee('2 docs in order')
        ->assertSee('IT');
});
