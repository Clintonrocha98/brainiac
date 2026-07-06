<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Projects\Pages\CreateProject;
use He4rt\Admin\Filament\Resources\Projects\Pages\EditProject;
use He4rt\Admin\Filament\Resources\Projects\Pages\ListProjects;
use He4rt\Admin\Filament\Resources\Projects\ProjectResource;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Permissions\Roles;
use He4rt\Identity\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    artisan('sync:permissions');

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list projects', function (): void {
    $projects = Project::factory()->count(3)->create();

    livewire(ListProjects::class)
        ->loadTable()
        ->assertCanSeeTableRecords($projects);
});

it('can create a project', function (): void {
    livewire(CreateProject::class)
        ->fillForm([
            'business_name' => 'Pagamentos Core',
            'technical_name' => 'payments-core',
            'slug' => 'payments-core',
            'acronym' => 'PAY',
            'repo_url' => 'https://github.com/3pontos/payments-core',
            'default_branch' => 'main',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Project::class, [
        'business_name' => 'Pagamentos Core',
        'acronym' => 'PAY',
    ]);
});

it('can render the edit project page without exposing credentials', function (): void {
    $project = Project::factory()->create([
        'webhook_token' => 'token-super-secreto',
        'hmac_secret' => 'hmac-super-secreto',
    ]);

    get(ProjectResource::getUrl('edit', ['record' => $project]))
        ->assertSuccessful()
        ->assertDontSee('token-super-secreto')
        ->assertDontSee('hmac-super-secreto');
});

it('rotates federation credentials revealing them a single time', function (): void {
    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->id])
        ->callAction('rotateFederationCredentials')
        ->assertNotified();

    $project->refresh();

    expect($project->webhook_token)->toHaveLength(48)
        ->and($project->hmac_secret)->toHaveLength(64);
});
