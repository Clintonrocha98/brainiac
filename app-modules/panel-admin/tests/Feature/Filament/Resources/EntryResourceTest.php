<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Entries\EntryResource;
use He4rt\Admin\Filament\Resources\Entries\Pages\CreateEntry;
use He4rt\Admin\Filament\Resources\Entries\Pages\EditEntry;
use He4rt\Admin\Filament\Resources\Entries\Pages\ListEntries;
use He4rt\Admin\Filament\Resources\Entries\RelationManagers\PrdVersionsRelationManager;
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
use He4rt\Identity\Permissions\Roles;
use He4rt\Identity\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    artisan('sync:permissions');

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list entries', function (): void {
    $entries = Entry::factory()->count(3)->create();

    livewire(ListEntries::class)
        ->loadTable()
        ->assertCanSeeTableRecords($entries);
});

it('can render the create entry page', function (): void {
    get(EntryResource::getUrl('create'))
        ->assertSuccessful();
});

it('creates a native entry minting the qualified id and saving the body', function (): void {
    $owner = User::factory()->create();
    $subjectProject = Project::factory()->create();

    livewire(CreateEntry::class)
        ->fillForm([
            'native_id' => 'onboarding/how-to/publicar-doc',
            'title' => 'Publicar Um Documento',
            'summary' => 'Guia de publicação.',
            'purpose' => Purpose::HowTo->value,
            'format' => Format::HowTo->value,
            'department' => Area::Product->value,
            'status' => Status::Draft->value,
            'audience' => [Audience::All->value],
            'owner_id' => $owner->id,
            'subject_project_ids' => [$subjectProject->id],
            'keywords' => ['fluxo'],
            'body_markdown' => '## Passos',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $entry = Entry::query()->where('native_id', 'onboarding/how-to/publicar-doc')->firstOrFail();

    expect($entry->qualified_id)->toBe('PRODUCT:onboarding/how-to/publicar-doc')
        ->and($entry->origin)->toBe(Origin::Native)
        ->and($entry->document?->body_markdown)->toBe('## Passos')
        ->and($entry->projects->pluck('id')->all())->toContain($subjectProject->id);
});

it('updates a native entry through the domain action', function (): void {
    $entry = Entry::factory()->create(['department' => Area::Design]);

    livewire(EditEntry::class, ['record' => $entry->id])
        ->fillForm([
            'title' => 'Titulo Atualizado',
            'body_markdown' => '## Corpo novo',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $entry->refresh();

    expect($entry->title)->toBe('Titulo Atualizado')
        ->and($entry->document?->body_markdown)->toBe('## Corpo novo');
});

it('does not allow editing or deleting mirror entries', function (): void {
    $mirror = Entry::factory()->create(['origin' => Origin::Mirror, 'owner_id' => null]);
    $native = Entry::factory()->create();

    expect(EntryResource::canEdit($mirror))->toBeFalse()
        ->and(EntryResource::canDelete($mirror))->toBeFalse()
        ->and(EntryResource::canEdit($native))->toBeTrue();
});

it('creates and freezes prd versions from the relation manager', function (): void {
    $prdEntry = Entry::factory()->prd()->create();
    $draftVersion = PrdVersion::factory()->create(['entry_id' => $prdEntry->id, 'major' => 2, 'minor' => 1]);

    livewire(PrdVersionsRelationManager::class, [
        'ownerRecord' => $prdEntry,
        'pageClass' => EditEntry::class,
    ])
        ->callAction(TestAction::make('freeze')->table($draftVersion));

    expect($draftVersion->refresh()->state)->toBe(PrdVersionState::Frozen)
        ->and($draftVersion->frozen_at)->not->toBeNull();
});

it('hides the prd relation manager for non prd entries', function (): void {
    $regularEntry = Entry::factory()->create(['format' => Format::Explanation]);
    $prdEntry = Entry::factory()->prd()->create();

    expect(PrdVersionsRelationManager::canViewForRecord($regularEntry, EditEntry::class))->toBeFalse()
        ->and(PrdVersionsRelationManager::canViewForRecord($prdEntry, EditEntry::class))->toBeTrue();
});
