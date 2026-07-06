<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Collections\Pages\CreateCollection;
use He4rt\Admin\Filament\Resources\Collections\Pages\EditCollection;
use He4rt\Admin\Filament\Resources\Collections\Pages\ListCollections;
use He4rt\Admin\Filament\Resources\Collections\RelationManagers\EntriesRelationManager;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Entry;
use He4rt\Identity\Permissions\Roles;
use He4rt\Identity\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    artisan('sync:permissions');

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list collections', function (): void {
    $collections = Collection::factory()->count(3)->create();

    livewire(ListCollections::class)
        ->loadTable()
        ->assertCanSeeTableRecords($collections);
});

it('can create a collection', function (): void {
    $owner = User::factory()->create();

    livewire(CreateCollection::class)
        ->fillForm([
            'title' => 'Onboarding De Devs',
            'slug' => 'onboarding-devs',
            'summary' => 'O caminho mínimo para contribuir na primeira semana.',
            'audience' => [Audience::Ti->value],
            'owner_id' => $owner->id,
            'status' => Status::Published->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Collection::class, [
        'title' => 'Onboarding De Devs',
        'slug' => 'onboarding-devs',
    ]);
});

it('attaches entries to the trail with the next position', function (): void {
    $collection = Collection::factory()->create();
    $alreadyInTrail = Entry::factory()->create();
    $collection->entries()->attach([$alreadyInTrail->id => ['position' => 1]]);

    $newEntry = Entry::factory()->create();

    livewire(EntriesRelationManager::class, [
        'ownerRecord' => $collection,
        'pageClass' => EditCollection::class,
    ])
        ->callAction(TestAction::make('attach')->table(), ['recordId' => $newEntry->id]);

    $trailEntries = $collection->refresh()->entries;

    expect($trailEntries)->toHaveCount(2)
        ->and($trailEntries->last()->id)->toBe($newEntry->id)
        ->and($trailEntries->last()->pivot->position)->toBe(2);
});
