<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;
use He4rt\Catalog\Models\PrdVersion;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;

beforeEach(function (): void {
    $this->withoutVite();
    $this->actingAs(User::factory()->create(['locale' => 'en']));
});

/**
 * @return array{0: Project, 1: Entry}
 */
function mirrorEntryInProject(): array
{
    $project = Project::factory()->create([
        'repo_url' => 'https://github.com/3pontos/rank-query',
        'default_branch' => 'develop',
    ]);

    $entry = Entry::factory()->create([
        'project_id' => $project->id,
        'origin' => Origin::Mirror,
        'owner_id' => null,
        'authors' => ['ana-souza', 'rmarinho'],
    ]);

    Document::factory()->create([
        'entry_id' => $entry->id,
        'body_markdown' => "## Contexto\n\nTexto do corpo.",
        'git_pointer' => 'docs/adr/0003.md',
    ]);

    return [$project, $entry];
}

function projectEntryUrl(Project $project, Entry $entry): string
{
    return sprintf('/portal/projects/%s/e/%s', $project->slug, $entry->id);
}

function areaEntryUrl(string $area, Entry $entry): string
{
    return sprintf('/portal/areas/%s/e/%s', $area, $entry->id);
}

test('renders a mirror doc with banner, authors and source link', function (): void {
    [$project, $entry] = mirrorEntryInProject();

    $this->get(projectEntryUrl($project, $entry))
        ->assertOk()
        ->assertSee('<h2 id="contexto">Contexto</h2>', false)
        ->assertSee('Texto do corpo')
        ->assertSee('mirrored from federation')
        ->assertSee('@ana-souza')
        ->assertSee('@rmarinho')
        ->assertSee('View source')
        ->assertSee('https://github.com/3pontos/rank-query/blob/develop/docs/adr/0003.md')
        ->assertSee('docs/adr/0003.md');
});

test('native doc shows the owner and hides banner and source link', function (): void {
    $owner = User::factory()->create(['name' => 'Camila Duarte']);
    $entry = Entry::factory()->create([
        'origin' => Origin::Native,
        'owner_id' => $owner->id,
        'department' => Area::Product,
    ]);
    Document::factory()->create(['entry_id' => $entry->id, 'body_markdown' => 'Corpo nativo.']);

    $this->get(areaEntryUrl('product', $entry))
        ->assertOk()
        ->assertSee('Camila Duarte')
        ->assertSee('Corpo nativo.')
        ->assertDontSee('mirrored from federation')
        ->assertDontSee('View source');
});

test('mirror without repo url hides the source link', function (): void {
    $project = Project::factory()->create(['repo_url' => null]);
    $entry = Entry::factory()->create(['project_id' => $project->id, 'origin' => Origin::Mirror, 'owner_id' => null]);
    Document::factory()->create(['entry_id' => $entry->id, 'git_pointer' => 'docs/x.md']);

    $this->get(projectEntryUrl($project, $entry))
        ->assertOk()
        ->assertDontSee('View source');
});

test('entry without document shows the empty body state', function (): void {
    $entry = Entry::factory()->create(['department' => Area::Marketing]);

    $this->get(areaEntryUrl('marketing', $entry))
        ->assertOk()
        ->assertSee('No document yet');
});

test('entry outside the context returns 404', function (): void {
    $entry = Entry::factory()->create(['department' => Area::Design]);

    $this->get(areaEntryUrl('ti', $entry))->assertNotFound();
});

test('typed links show directional labels on both ends', function (): void {
    $from = Entry::factory()->create(['department' => Area::Design, 'title' => 'Prd Do Catalogo']);
    $to = Entry::factory()->create(['department' => Area::Design, 'title' => 'Adr Da Wiki']);
    EntryLink::factory()->create(['from_entry_id' => $from->id, 'to_entry_id' => $to->id, 'type' => EntryLinkType::Supersedes]);

    $this->get(areaEntryUrl('design', $from))
        ->assertOk()
        ->assertSee('Supersedes')
        ->assertSee('Adr Da Wiki');

    $this->get(areaEntryUrl('design', $to))
        ->assertOk()
        ->assertSee('Superseded by')
        ->assertSee('Prd Do Catalogo');
});

test('previous and next follow the trail order', function (): void {
    $collection = Collection::factory()->create();
    [$first, $middle, $last] = Entry::factory()->count(3)->create();
    $collection->entries()->attach([
        $first->id => ['position' => 1],
        $middle->id => ['position' => 2],
        $last->id => ['position' => 3],
    ]);

    $this->get(sprintf('/portal/collections/%s/e/%s', $collection->slug, $middle->id))
        ->assertOk()
        ->assertSee('← Previous')
        ->assertSee($first->title)
        ->assertSee('Next →')
        ->assertSee($last->title);
});

test('prd shows the latest version by default and can pin an old one', function (): void {
    $entry = Entry::factory()->prd()->create(['department' => Area::Product]);
    PrdVersion::factory()->frozen()->create([
        'entry_id' => $entry->id,
        'major' => 2,
        'minor' => 0,
        'body_markdown' => '## Conteudo Antigo',
    ]);
    PrdVersion::factory()->create([
        'entry_id' => $entry->id,
        'major' => 2,
        'minor' => 1,
        'body_markdown' => '## Conteudo Novo',
    ]);

    $url = areaEntryUrl('product', $entry);

    $this->get($url)
        ->assertOk()
        ->assertSee('v2.1')
        ->assertSee('Conteudo Novo')
        ->assertDontSee('Conteudo Antigo')
        ->assertDontSee('You are reading version');

    $this->get($url.'?v=v2.0')
        ->assertOk()
        ->assertSee('Conteudo Antigo')
        ->assertDontSee('Conteudo Novo')
        ->assertSee('You are reading version');
});
