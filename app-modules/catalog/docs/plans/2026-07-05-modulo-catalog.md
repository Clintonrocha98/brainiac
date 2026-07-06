---
type: plan
title: "Implementação do módulo catalog (domínio + federação)"
module: catalog
status: proposed
date: 2026-07-05
author: clintonrocha
related:
  spec: docs/specs/2026-07-05-modelagem-de-dados-do-catalogo.md
  adr: docs/adr/0014-dois-modulos-catalog-e-apresentacao.md
---

# Implementação do módulo catalog — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir o módulo de domínio `catalog` — o agregado do catálogo (Entry como raiz) mais a federação — com schema, models, enums, factories e a reconciliação de snapshot, tudo coberto por testes.

**Architecture:** Módulo `internachi/modular` `He4rt\Catalog\` (domínio). Entry é a raiz de agregado; conteúdo polimórfico (`documents` 1:1, `prd_versions` 1:N); vocabulários como PHP enums; facetas multi-valor em pivots. A federação (ingest por PUSH) vive no sub-namespace `Federation/` e escreve nas tabelas do próprio módulo. Sem tenancy (single-tenant, ADR-0013). Ver [Modelagem de dados do catálogo do Brainiac](../../../../docs/specs/2026-07-05-modelagem-de-dados-do-catalogo.md) e [Brainiac se organiza em dois módulos](../../../../docs/adr/0014-dois-modulos-catalog-e-apresentacao.md).

**Tech Stack:** PHP 8.5, Laravel 13, PostgreSQL, `internachi/modular`, Pest 4, PHPStan/Larastan, spatie/laravel-activitylog.

## Global Constraints

- **PK = UUID** em toda tabela. Models estendem `App\Models\BaseModel` (traz `HasUuids`, `HasFactory`, `HasActivity`, `InteractsWithRequest`). Migrations: `$table->uuid('id')->primary()`.
- **Colunas de data/hora sempre `Tz`**: `timestampsTz()`, `timestampTz()`. Nunca `timestamps()`/`timestamp()`. (guideline `timezone-aware-dates`)
- **Migrations só via `php artisan make:migration ... --module=catalog`**; nunca criar o arquivo à mão. (guideline `timezone-aware-dates`)
- **Model PHPDoc obrigatório**: bloco `/** @property ... */` cobrindo toda coluna, `#[Table(name: '...')]` explícito, `#[UseFactory(XxxFactory::class)]`. (guideline `model-phpdoc-sync`)
- **Nomes de código em inglês claro**; docs/glossário em pt_BR. Enums: cases em inglês, labels via i18n pt_BR (`__('catalog::...')`). (memória `nomenclatura-codigo-en-docs-ptbr`)
- **Tabelas prefixadas `catalog_`** (espelha `identity_`).
- **Pint + PHPStan antes de cada commit**: `vendor/bin/pint --dirty --format agent` e `vendor/bin/phpstan analyse` (rodar de dentro de `app-modules/catalog/` ou apontando o path).
- **Pest**: `nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact` (nunca `--parallel` sem `--processes`). Para um arquivo: `php artisan test --compact --filter=...`.
- **Nunca** adicionar `Co-Authored-By` em commits.

---

## File Structure

```
app-modules/catalog/
├── composer.json                              # psr-4 He4rt\Catalog\, provider
├── phpstan.neon  +  phpstan.ignore.neon
├── src/
│   ├── CatalogServiceProvider.php             # migrations, translations, morphMap
│   ├── Enums/
│   │   ├── Purpose.php  Format.php  Origin.php
│   │   ├── Area.php  Audience.php  Status.php
│   │   ├── EntryLinkType.php  PrdVersionState.php
│   ├── Models/
│   │   ├── Project.php  Entry.php  Document.php  PrdVersion.php
│   │   ├── EntryLink.php  EntryArtifact.php  Collection.php
│   ├── DTOs/
│   │   ├── BodyFacts.php                       # readonly: has_image/…/mentions
│   │   └── Snapshot.php  SnapshotEntry.php      # payload da federação
│   ├── Actions/
│   │   ├── DeriveBodyFacts.php                  # markdown → BodyFacts
│   │   └── MintQualifiedId.php                  # (origin|area)+native_id → id
│   └── Federation/
│       ├── ReconcileSnapshot.php               # upsert + delete-absent (tx)
│       ├── VerifyWebhookSignature.php          # HMAC
│       └── Http/ReceiveSnapshotController.php   # rota webhook
├── database/
│   ├── migrations/                             # arquivos *_catalog_*.php (flat)
│   └── factories/                              # uma factory por model
├── lang/{en,pt_BR}/enums.php                   # labels dos enums
├── routes/federation-routes.php                # POST webhook
└── tests/{Unit,Feature}/
```

**Dependência de módulo:** `catalog` usa `He4rt\Identity\Users\User` (owner). É domínio→domínio (permitido). Sem require explícito: os módulos são path-repos autoload no root.

---

## Task 1: Skeleton do módulo

**Files:**
- Create: `app-modules/catalog/composer.json`, `app-modules/catalog/src/CatalogServiceProvider.php`, `app-modules/catalog/phpstan.neon`, `app-modules/catalog/phpstan.ignore.neon`
- Create: `app-modules/catalog/tests/Unit/.gitkeep`, `app-modules/catalog/tests/Feature/.gitkeep`
- Test: `app-modules/catalog/tests/Feature/ModuleBootTest.php`

**Interfaces:**
- Produces: módulo `He4rt\Catalog\` autoloadável; `CatalogServiceProvider` carregando migrations de `database/migrations` e translations no namespace `catalog`.

- [ ] **Step 1: Gerar o módulo via Artisan**

Run:
```bash
php artisan make:module catalog --no-interaction
php artisan modules:list
```
Expected: `catalog` aparece na lista. Cria `app-modules/catalog/` com `composer.json`, `src/CatalogServiceProvider.php`, `tests/`.

- [ ] **Step 2: Ajustar `composer.json` do módulo**

Sobrescrever `app-modules/catalog/composer.json`:
```json
{
    "name": "he4rt/catalog",
    "description": "",
    "type": "library",
    "version": "1.0.0",
    "license": "proprietary",
    "autoload": {
        "psr-4": {
            "He4rt\\Catalog\\": "src/",
            "He4rt\\Catalog\\Database\\Factories\\": "database/factories/",
            "He4rt\\Catalog\\Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "He4rt\\Catalog\\Tests\\": "tests/"
        }
    },
    "minimum-stability": "stable",
    "extra": {
        "laravel": {
            "providers": [
                "He4rt\\Catalog\\CatalogServiceProvider"
            ]
        }
    }
}
```

- [ ] **Step 3: Registrar o módulo no root e regenerar autoload**

Run:
```bash
composer require he4rt/catalog:^1.0.0 --no-interaction
composer dump-autoload
php artisan modules:list
```
Expected: `he4rt/catalog` em `require` do `composer.json` root; sem erro de autoload. (Se `make:module` já adicionou o require, `composer update he4rt/catalog` basta.)

- [ ] **Step 4: Escrever o ServiceProvider canônico**

`app-modules/catalog/src/CatalogServiceProvider.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog;

use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;
use He4rt\Catalog\Models\EntryLink;
use He4rt\Catalog\Models\PrdVersion;
use He4rt\Catalog\Models\Project;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

final class CatalogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'catalog');
        $this->loadRoutesFrom(__DIR__.'/../routes/federation-routes.php');

        Relation::enforceMorphMap([
            'project' => Project::class,
            'entry' => Entry::class,
            'document' => Document::class,
            'prd_version' => PrdVersion::class,
            'entry_link' => EntryLink::class,
            'entry_artifact' => EntryArtifact::class,
            'collection' => Collection::class,
        ]);
    }
}
```
> `routes/federation-routes.php` é criado na Task 12; até lá, criar o arquivo com `<?php` + `declare(strict_types=1);` vazio para o boot não quebrar (Step 6).

- [ ] **Step 5: phpstan do módulo**

`app-modules/catalog/phpstan.neon`:
```neon
includes:
    - phpstan.ignore.neon

parameters:
    paths:
        - src/
```
`app-modules/catalog/phpstan.ignore.neon`:
```neon
parameters:
    ignoreErrors:
```

- [ ] **Step 6: Placeholder de rotas + escrever o teste de boot**

`app-modules/catalog/routes/federation-routes.php`:
```php
<?php

declare(strict_types=1);
```
`app-modules/catalog/tests/Feature/ModuleBootTest.php`:
```php
<?php

declare(strict_types=1);

test('catalog service provider is registered', function (): void {
    expect(app()->getProviders(\He4rt\Catalog\CatalogServiceProvider::class))
        ->not->toBeEmpty();
});
```

- [ ] **Step 7: Rodar o teste**

Run: `php artisan test --compact --filter=ModuleBootTest`
Expected: PASS (1 passed).

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/catalog composer.json composer.lock
git commit -m "feat(catalog): scaffold do módulo de domínio"
```

---

## Task 2: Enums (vocabulários controlados) + i18n

**Files:**
- Create: `src/Enums/Purpose.php`, `Format.php`, `Origin.php`, `Area.php`, `Audience.php`, `Status.php`, `EntryLinkType.php`, `PrdVersionState.php`
- Create: `lang/en/enums.php`, `lang/pt_BR/enums.php`
- Test: `tests/Unit/Enums/EnumsTest.php`

**Interfaces:**
- Produces: `Purpose`, `Format`, `Origin`, `Area`, `Audience`, `Status`, `EntryLinkType`, `PrdVersionState` (todos `enum: string`); `HasLabel` com `getLabel()` traduzido.

- [ ] **Step 1: Escrever o teste dos enums**

`tests/Unit/Enums/EnumsTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;

test('purpose has the three canonical values', function (): void {
    expect(array_column(Purpose::cases(), 'value'))
        ->toBe(['reference', 'how-to', 'explanation']);
});

test('format carries the how-to value with a hyphen', function (): void {
    expect(Format::HowTo->value)->toBe('how-to');
});

test('origin distinguishes native from mirror', function (): void {
    expect(array_column(Origin::cases(), 'value'))->toBe(['native', 'mirror']);
});

test('audience is a superset of area plus all and external', function (): void {
    $audience = array_column(Audience::cases(), 'value');

    foreach (Area::cases() as $area) {
        expect($audience)->toContain($area->value);
    }
    expect($audience)->toContain('all')->toContain('external');
});

test('label is resolved through translation', function (): void {
    app()->setLocale('pt_BR');
    expect(Status::Published->getLabel())->toBe('Publicado');
});
```

- [ ] **Step 2: Rodar o teste (falha)**

Run: `php artisan test --compact --filter=EnumsTest`
Expected: FAIL ("Class ... not found").

- [ ] **Step 3: Escrever os enums**

`src/Enums/Purpose.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Purpose: string implements HasLabel
{
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';

    public function getLabel(): string
    {
        return __("catalog::enums.purpose.{$this->value}");
    }
}
```
`src/Enums/Format.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Format: string implements HasLabel
{
    case Readme = 'readme';
    case Context = 'context';
    case Reference = 'reference';
    case HowTo = 'how-to';
    case Explanation = 'explanation';
    case Adr = 'adr';
    case Spec = 'spec';
    case Plan = 'plan';
    case Prd = 'prd';

    public function getLabel(): string
    {
        return __("catalog::enums.format.{$this->value}");
    }

    public function isPrd(): bool
    {
        return $this === self::Prd;
    }
}
```
`src/Enums/Origin.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Origin: string implements HasLabel
{
    case Native = 'native';
    case Mirror = 'mirror';

    public function getLabel(): string
    {
        return __("catalog::enums.origin.{$this->value}");
    }
}
```
`src/Enums/Area.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Area: string implements HasLabel
{
    case Ti = 'ti';
    case Business = 'business';
    case Product = 'product';
    case Marketing = 'marketing';
    case Design = 'design';

    public function getLabel(): string
    {
        return __("catalog::enums.area.{$this->value}");
    }
}
```
`src/Enums/Audience.php` (superconjunto de `Area` + `all` + `external`):
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Audience: string implements HasLabel
{
    case Ti = 'ti';
    case Business = 'business';
    case Product = 'product';
    case Marketing = 'marketing';
    case Design = 'design';
    case All = 'all';
    case External = 'external';

    public function getLabel(): string
    {
        return __("catalog::enums.audience.{$this->value}");
    }
}
```
`src/Enums/Status.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasLabel
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Obsolete = 'obsolete';

    public function getLabel(): string
    {
        return __("catalog::enums.status.{$this->value}");
    }
}
```
`src/Enums/EntryLinkType.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum EntryLinkType: string implements HasLabel
{
    case Supersedes = 'supersedes';
    case Related = 'related';
    case DependsOn = 'depends_on';
    case PartOf = 'part_of';

    public function getLabel(): string
    {
        return __("catalog::enums.entry_link_type.{$this->value}");
    }
}
```
`src/Enums/PrdVersionState.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Enums;

use Filament\Support\Contracts\HasLabel;

enum PrdVersionState: string implements HasLabel
{
    case Draft = 'draft';
    case Frozen = 'frozen';

    public function getLabel(): string
    {
        return __("catalog::enums.prd_version_state.{$this->value}");
    }
}
```

- [ ] **Step 4: Escrever os arquivos de tradução**

`lang/pt_BR/enums.php`:
```php
<?php

declare(strict_types=1);

return [
    'purpose' => ['reference' => 'Referência', 'how-to' => 'How-to', 'explanation' => 'Explicação'],
    'format' => [
        'readme' => 'README', 'context' => 'CONTEXT', 'reference' => 'Referência',
        'how-to' => 'How-to', 'explanation' => 'Explicação', 'adr' => 'ADR',
        'spec' => 'Spec', 'plan' => 'Plano', 'prd' => 'PRD',
    ],
    'origin' => ['native' => 'Nativo', 'mirror' => 'Espelho'],
    'area' => ['ti' => 'TI', 'business' => 'Negócio', 'product' => 'Produto', 'marketing' => 'Marketing', 'design' => 'Design'],
    'audience' => [
        'ti' => 'TI', 'business' => 'Negócio', 'product' => 'Produto', 'marketing' => 'Marketing',
        'design' => 'Design', 'all' => 'Todos', 'external' => 'Externo',
    ],
    'status' => ['draft' => 'Rascunho', 'review' => 'Revisão', 'published' => 'Publicado', 'obsolete' => 'Obsoleto'],
    'entry_link_type' => ['supersedes' => 'Substitui', 'related' => 'Relacionada', 'depends_on' => 'Depende de', 'part_of' => 'Parte de'],
    'prd_version_state' => ['draft' => 'Rascunho', 'frozen' => 'Congelada'],
];
```
`lang/en/enums.php` (mesmas chaves, valores em inglês):
```php
<?php

declare(strict_types=1);

return [
    'purpose' => ['reference' => 'Reference', 'how-to' => 'How-to', 'explanation' => 'Explanation'],
    'format' => [
        'readme' => 'README', 'context' => 'CONTEXT', 'reference' => 'Reference',
        'how-to' => 'How-to', 'explanation' => 'Explanation', 'adr' => 'ADR',
        'spec' => 'Spec', 'plan' => 'Plan', 'prd' => 'PRD',
    ],
    'origin' => ['native' => 'Native', 'mirror' => 'Mirror'],
    'area' => ['ti' => 'IT', 'business' => 'Business', 'product' => 'Product', 'marketing' => 'Marketing', 'design' => 'Design'],
    'audience' => [
        'ti' => 'IT', 'business' => 'Business', 'product' => 'Product', 'marketing' => 'Marketing',
        'design' => 'Design', 'all' => 'All', 'external' => 'External',
    ],
    'status' => ['draft' => 'Draft', 'review' => 'Review', 'published' => 'Published', 'obsolete' => 'Obsolete'],
    'entry_link_type' => ['supersedes' => 'Supersedes', 'related' => 'Related', 'depends_on' => 'Depends on', 'part_of' => 'Part of'],
    'prd_version_state' => ['draft' => 'Draft', 'frozen' => 'Frozen'],
];
```

- [ ] **Step 5: Rodar o teste (passa)**

Run: `php artisan test --compact --filter=EnumsTest`
Expected: PASS.

- [ ] **Step 6: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): enums de vocabulário controlado + i18n"
```

---

## Task 3: Model Project

**Files:**
- Create migration (via artisan): `create_catalog_projects_table`
- Create: `src/Models/Project.php`, `database/factories/ProjectFactory.php`
- Test: `tests/Feature/Models/ProjectTest.php`

**Interfaces:**
- Produces: `Project` com `acronym` (único); relações `entries()` (origem) e `taggedEntries()` (faceta) — definidas na Task 4 quando `entries` existir. Nesta task só `entries()` origem fica declarada (aponta para tabela criada na Task 4; o teto da relação é exercitado na Task 4).

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/ProjectTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Project;
use Illuminate\Database\QueryException;

test('project can be created with factory', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    expect($project->acronym)->toBe('RPQ')
        ->and($project->id)->toBeString();
});

test('acronym is unique', function (): void {
    Project::factory()->create(['acronym' => 'RPQ']);
    Project::factory()->create(['acronym' => 'RPQ']);
})->throws(QueryException::class);
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=ProjectTest`
Expected: FAIL.

- [ ] **Step 3: Criar a migration**

Run: `php artisan make:migration create_catalog_projects_table --module=catalog`
Conteúdo do `up()`:
```php
Schema::create('catalog_projects', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('business_name');
    $table->string('technical_name');
    $table->string('slug')->unique();
    $table->string('acronym')->unique();
    $table->string('webhook_token')->nullable();   // hash
    $table->text('hmac_secret')->nullable();        // cifrado (cast encrypted)
    $table->timestampTz('last_synced_at')->nullable();
    $table->timestampsTz();
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/Project.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $business_name
 * @property string $technical_name
 * @property string $slug
 * @property string $acronym
 * @property string|null $webhook_token
 * @property string|null $hmac_secret
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Entry> $entries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Entry> $taggedEntries
 *
 * @extends BaseModel<ProjectFactory>
 */
#[UseFactory(ProjectFactory::class)]
#[Table(name: 'catalog_projects')]
final class Project extends BaseModel
{
    /** Entradas cuja ORIGEM (project_id) é este projeto. */
    /** @return HasMany<Entry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'project_id');
    }

    /** Entradas que este projeto é ASSUNTO (faceta, pivot). */
    /** @return BelongsToMany<Entry, $this> */
    public function taggedEntries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'catalog_entry_project');
    }

    protected function casts(): array
    {
        return [
            'hmac_secret' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }
}
```
> PHPDoc `@property @property-read` cobre toda coluna (guideline `model-phpdoc-sync`). `hmac_secret` cifrado via cast `encrypted`; `webhook_token` guarda hash (comparado na Task 12).

- [ ] **Step 5: Escrever a factory**

`database/factories/ProjectFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'business_name' => Str::title($name),
            'technical_name' => Str::slug($name),
            'slug' => Str::slug($name),
            'acronym' => Str::upper(fake()->unique()->lexify('???')),
            'webhook_token' => null,
            'hmac_secret' => null,
            'last_synced_at' => null,
        ];
    }
}
```

- [ ] **Step 6: Rodar (passa)**

Run: `php artisan test --compact --filter=ProjectTest`
Expected: PASS.

- [ ] **Step 7: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model Project"
```

---

## Task 4: Model Entry + faceta projeto + invariante origem-na-faceta + MintQualifiedId

**Files:**
- Create migrations: `create_catalog_entries_table`, `create_catalog_entry_project_table`
- Create: `src/Models/Entry.php`, `database/factories/EntryFactory.php`, `src/Actions/MintQualifiedId.php`
- Test: `tests/Feature/Models/EntryTest.php`, `tests/Unit/Actions/MintQualifiedIdTest.php`

**Interfaces:**
- Consumes: `Project` (Task 3), enums (Task 2), `He4rt\Identity\Users\User`.
- Produces: `Entry` com relações `originProject()`, `owner()`, `projects()` (faceta, `catalog_entry_project`); invariante: ao salvar com `project_id`, o projeto de origem é sincronizado na faceta. `MintQualifiedId::execute(?Project $origin, Area $department, string $nativeId): string`.

- [ ] **Step 1: Escrever os testes**

`tests/Unit/Actions/MintQualifiedIdTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Actions\MintQualifiedId;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Project;

test('prefixes with the origin project acronym', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    expect(app(MintQualifiedId::class)->execute($project, Area::Ti, 'PRD-12'))
        ->toBe('RPQ:PRD-12');
});

test('falls back to the department area when there is no origin', function (): void {
    expect(app(MintQualifiedId::class)->execute(null, Area::Design, 'how-to/handoff'))
        ->toBe('DESIGN:how-to/handoff');
});
```
`tests/Feature/Models/EntryTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use Illuminate\Database\QueryException;

test('entry can be created with factory and casts enums', function (): void {
    $entry = Entry::factory()->create([
        'purpose' => Purpose::Reference,
        'format' => Format::Prd,
        'origin' => Origin::Native,
        'department' => Area::Ti,
        'status' => Status::Published,
    ]);

    expect($entry->purpose)->toBe(Purpose::Reference)
        ->and($entry->format)->toBe(Format::Prd)
        ->and($entry->department)->toBe(Area::Ti);
});

test('qualified_id is unique', function (): void {
    Entry::factory()->create(['qualified_id' => 'RPQ:PRD-1']);
    Entry::factory()->create(['qualified_id' => 'RPQ:PRD-1']);
})->throws(QueryException::class);

test('origin project is auto-synced into the projeto facet', function (): void {
    $project = Project::factory()->create();

    $entry = Entry::factory()->for($project, 'originProject')->create();

    expect($entry->projects()->pluck('catalog_projects.id'))
        ->toContain($project->id);
});

test('owner resolves to the identity user', function (): void {
    $user = User::factory()->create();
    $entry = Entry::factory()->for($user, 'owner')->create();

    expect($entry->owner->is($user))->toBeTrue();
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter="EntryTest|MintQualifiedIdTest"`
Expected: FAIL.

- [ ] **Step 3: Criar as migrations**

Run:
```bash
php artisan make:migration create_catalog_entries_table --module=catalog
php artisan make:migration create_catalog_entry_project_table --module=catalog
```
`create_catalog_entries_table` `up()`:
```php
Schema::create('catalog_entries', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('qualified_id')->unique();
    $table->string('native_id');
    $table->foreignUuid('project_id')->nullable()->constrained('catalog_projects')->nullOnDelete();
    $table->string('slug')->nullable();
    $table->string('title');
    $table->text('summary');
    $table->string('purpose');
    $table->string('format');
    $table->string('origin');
    $table->string('department');
    $table->jsonb('audience');
    $table->jsonb('keywords')->nullable();
    $table->string('status');
    $table->foreignUuid('owner_id')->constrained('identity_users')->restrictOnDelete();
    $table->timestampsTz();

    $table->index(['project_id', 'origin']); // reconciliação da federação
});
```
`create_catalog_entry_project_table` `up()`:
```php
Schema::create('catalog_entry_project', static function (Blueprint $table): void {
    $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->foreignUuid('project_id')->constrained('catalog_projects')->cascadeOnDelete();
    $table->primary(['entry_id', 'project_id']);
});
```

- [ ] **Step 4: Escrever a Action MintQualifiedId**

`src/Actions/MintQualifiedId.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Project;
use Illuminate\Support\Str;

final class MintQualifiedId
{
    /**
     * Cunha o id qualificado: PREFIXO:native_id, onde o prefixo é a sigla do
     * projeto de origem ou, na ausência dele, o nome da Área (departamento).
     */
    public function execute(?Project $origin, Area $department, string $nativeId): string
    {
        $prefix = $origin?->acronym ?? Str::upper($department->value);

        return "{$prefix}:{$nativeId}";
    }
}
```

- [ ] **Step 5: Escrever o model Entry (com a invariante)**

`src/Models/Entry.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryFactory;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $qualified_id
 * @property string $native_id
 * @property string|null $project_id
 * @property string|null $slug
 * @property string $title
 * @property string $summary
 * @property Purpose $purpose
 * @property Format $format
 * @property Origin $origin
 * @property Area $department
 * @property \Illuminate\Support\Collection<int, Audience> $audience
 * @property array<int, string>|null $keywords
 * @property Status $status
 * @property string $owner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project|null $originProject
 * @property-read User $owner
 * @property-read Document|null $document
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PrdVersion> $prdVersions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EntryArtifact> $artifacts
 *
 * @extends BaseModel<EntryFactory>
 */
#[UseFactory(EntryFactory::class)]
#[Table(name: 'catalog_entries')]
final class Entry extends BaseModel
{
    protected static function booted(): void
    {
        // Invariante: a origem sempre está na faceta projeto (spec, entry_project).
        static::saved(static function (Entry $entry): void {
            if ($entry->project_id !== null) {
                $entry->projects()->syncWithoutDetaching([$entry->project_id]);
            }
        });
    }

    /** @return BelongsTo<Project, $this> */
    public function originProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasOne<Document, $this> */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }

    /** @return HasMany<PrdVersion, $this> */
    public function prdVersions(): HasMany
    {
        return $this->hasMany(PrdVersion::class);
    }

    /** Faceta projeto (assunto). @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'catalog_entry_project');
    }

    /** @return HasMany<EntryArtifact, $this> */
    public function artifacts(): HasMany
    {
        return $this->hasMany(EntryArtifact::class);
    }

    protected function casts(): array
    {
        return [
            'purpose' => Purpose::class,
            'format' => Format::class,
            'origin' => Origin::class,
            'department' => Area::class,
            'audience' => AsEnumCollection::of(Audience::class),
            'keywords' => 'array',
            'status' => Status::class,
        ];
    }
}
```

- [ ] **Step 6: Escrever a factory**

`database/factories/EntryFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Entry>
 */
final class EntryFactory extends Factory
{
    protected $model = Entry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $native = 'doc/'.fake()->unique()->slug(2);

        return [
            'qualified_id' => 'DESIGN:'.$native,
            'native_id' => $native,
            'project_id' => null,
            'slug' => Str::slug($native),
            'title' => Str::title(fake()->words(3, true)),
            'summary' => fake()->sentence(),
            'purpose' => fake()->randomElement(Purpose::cases()),
            'format' => Format::Explanation,
            'origin' => Origin::Native,
            'department' => Area::Design,
            'audience' => [Audience::Ti],
            'keywords' => ['exemplo'],
            'status' => Status::Published,
            'owner_id' => User::factory(),
        ];
    }

    public function prd(): self
    {
        return $this->state(fn (): array => [
            'format' => Format::Prd,
            'purpose' => Purpose::Reference,
        ]);
    }
}
```

- [ ] **Step 7: Rodar (passa)**

Run: `php artisan test --compact --filter="EntryTest|MintQualifiedIdTest"`
Expected: PASS.

- [ ] **Step 8: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model Entry, faceta projeto e cunhagem de id"
```

---

## Task 5: BodyFacts DTO + DeriveBodyFacts action

**Files:**
- Create: `src/DTOs/BodyFacts.php`, `src/Actions/DeriveBodyFacts.php`
- Test: `tests/Unit/Actions/DeriveBodyFactsTest.php`

**Interfaces:**
- Produces: `BodyFacts` (readonly: `hasImage`, `hasMermaid`, `hasArtifact`, `mentions`) e `DeriveBodyFacts::execute(string $markdown): BodyFacts`. Consumido por Document (Task 6), PrdVersion (Task 7) e Collection (Task 10) no save, e pela federação (Task 11).

- [ ] **Step 1: Escrever o teste**

`tests/Unit/Actions/DeriveBodyFactsTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Actions\DeriveBodyFacts;

test('detects image, mermaid and mentions in the body', function (): void {
    $markdown = <<<'MD'
    # Título
    ![diagrama](foto.png)
    ```mermaid
    graph TD; A-->B;
    ```
    Veja [o módulo](RPQ:pagamentos/reference/modulo) e o arquivo repo://docs/x.md.
    MD;

    $facts = app(DeriveBodyFacts::class)->execute($markdown);

    expect($facts->hasImage)->toBeTrue()
        ->and($facts->hasMermaid)->toBeTrue()
        ->and($facts->mentions)->toContain('RPQ:pagamentos/reference/modulo');
});

test('empty body yields all-false facts', function (): void {
    $facts = app(DeriveBodyFacts::class)->execute('texto simples');

    expect($facts->hasImage)->toBeFalse()
        ->and($facts->hasMermaid)->toBeFalse()
        ->and($facts->hasArtifact)->toBeFalse()
        ->and($facts->mentions)->toBe([]);
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=DeriveBodyFactsTest`
Expected: FAIL.

- [ ] **Step 3: Escrever o DTO**

`src/DTOs/BodyFacts.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

final readonly class BodyFacts
{
    /**
     * @param  array<int, string>  $mentions  ids/paths citados no corpo
     */
    public function __construct(
        public bool $hasImage,
        public bool $hasMermaid,
        public bool $hasArtifact,
        public array $mentions,
    ) {}

    /**
     * @return array{has_image: bool, has_mermaid: bool, has_artifact: bool, mentions: array<int, string>}
     */
    public function toColumns(): array
    {
        return [
            'has_image' => $this->hasImage,
            'has_mermaid' => $this->hasMermaid,
            'has_artifact' => $this->hasArtifact,
            'mentions' => $this->mentions,
        ];
    }
}
```

- [ ] **Step 4: Escrever a action**

`src/Actions/DeriveBodyFacts.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\DTOs\BodyFacts;

final class DeriveBodyFacts
{
    public function execute(string $markdown): BodyFacts
    {
        $hasImage = str_contains($markdown, '![');
        $hasMermaid = (bool) preg_match('/```mermaid/i', $markdown);

        // Destinos de links markdown [texto](destino).
        preg_match_all('/\[[^\]]*\]\(([^)]+)\)/', $markdown, $matches);
        $targets = $matches[1] ?? [];

        // Menções = links para outras Entradas: id qualificado (PREFIX:...) ou repo path.
        $mentions = array_values(array_filter($targets, static fn (string $t): bool =>
            (bool) preg_match('/^[A-Z0-9]+:/', $t) || str_starts_with($t, 'repo://')
        ));

        // Artefato: algum destino aponta para um asset HTML.
        $hasArtifact = (bool) array_filter($targets, static fn (string $t): bool =>
            str_ends_with(strtok($t, '?#') ?: $t, '.html')
        );

        return new BodyFacts($hasImage, $hasMermaid, $hasArtifact, $mentions);
    }
}
```
> Detecção por heurística de texto (rápida, sem IA) — coerente com a decisão de derivar fatos do corpo no save/ingest.

- [ ] **Step 5: Rodar (passa)**

Run: `php artisan test --compact --filter=DeriveBodyFactsTest`
Expected: PASS.

- [ ] **Step 6: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): BodyFacts + DeriveBodyFacts"
```

---

## Task 6: Model Document (corpo único, 1:1)

**Files:**
- Create migration: `create_catalog_documents_table`
- Create: `src/Models/Document.php`, `database/factories/DocumentFactory.php`
- Test: `tests/Feature/Models/DocumentTest.php`

**Interfaces:**
- Consumes: `Entry` (Task 4), `DeriveBodyFacts` (Task 5).
- Produces: `Document` com `entry()` BelongsTo; fatos do corpo derivados no save (`saving`).

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/DocumentTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;

test('document belongs to an entry and derives body facts on save', function (): void {
    $entry = Entry::factory()->create();

    $document = Document::factory()->for($entry)->create([
        'body_markdown' => "![x](a.png)\n```mermaid\ngraph TD;A-->B;\n```",
    ]);

    expect($document->entry->is($entry))->toBeTrue()
        ->and($document->has_image)->toBeTrue()
        ->and($document->has_mermaid)->toBeTrue();
});

test('mirror document keeps a git pointer', function (): void {
    $document = Document::factory()->create(['git_pointer' => 'docs/x.md']);

    expect($document->git_pointer)->toBe('docs/x.md');
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=DocumentTest`
Expected: FAIL.

- [ ] **Step 3: Criar a migration**

Run: `php artisan make:migration create_catalog_documents_table --module=catalog`
`up()`:
```php
Schema::create('catalog_documents', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('entry_id')->unique()->constrained('catalog_entries')->cascadeOnDelete();
    $table->text('body_markdown');
    $table->string('git_pointer')->nullable();
    $table->boolean('has_image')->default(false);
    $table->boolean('has_mermaid')->default(false);
    $table->boolean('has_artifact')->default(false);
    $table->jsonb('mentions')->nullable();
    $table->timestampsTz();
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/Document.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $entry_id
 * @property string $body_markdown
 * @property string|null $git_pointer
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<DocumentFactory>
 */
#[UseFactory(DocumentFactory::class)]
#[Table(name: 'catalog_documents')]
final class Document extends BaseModel
{
    protected static function booted(): void
    {
        static::saving(static function (Document $document): void {
            if ($document->isDirty('body_markdown')) {
                $document->fill(app(DeriveBodyFacts::class)->execute($document->body_markdown)->toColumns());
            }
        });
    }

    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Não loga o corpo inteiro no activity log (evita bloat).
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\Support\LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected function casts(): array
    {
        return [
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
```

- [ ] **Step 5: Escrever a factory**

`database/factories/DocumentFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Document;
use He4rt\Catalog\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
final class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'body_markdown' => fake()->paragraphs(3, true),
            'git_pointer' => null,
        ];
    }
}
```

- [ ] **Step 6: Rodar (passa)**

Run: `php artisan test --compact --filter=DocumentTest`
Expected: PASS.

- [ ] **Step 7: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model Document com derivação de fatos do corpo"
```

---

## Task 7: Model PrdVersion (pilha de versões)

**Files:**
- Create migration: `create_catalog_prd_versions_table`
- Create: `src/Models/PrdVersion.php`, `database/factories/PrdVersionFactory.php`
- Test: `tests/Feature/Models/PrdVersionTest.php`

**Interfaces:**
- Consumes: `Entry` (Task 4), `DeriveBodyFacts` (Task 5), `PrdVersionState` (Task 2).
- Produces: `PrdVersion` com `entry()` BelongsTo; estado `draft|frozen`; deriva fatos do corpo no save.

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/PrdVersionTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;

test('a prd entry can hold several versions', function (): void {
    $entry = Entry::factory()->prd()->create();

    PrdVersion::factory()->for($entry)->frozen()->create(['major' => 1, 'minor' => 0]);
    PrdVersion::factory()->for($entry)->create(); // draft

    expect($entry->prdVersions()->count())->toBe(2);
});

test('a frozen version records its state and timestamp', function (): void {
    $version = PrdVersion::factory()->frozen()->create();

    expect($version->state)->toBe(PrdVersionState::Frozen)
        ->and($version->frozen_at)->not->toBeNull();
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=PrdVersionTest`
Expected: FAIL.

- [ ] **Step 3: Criar a migration**

Run: `php artisan make:migration create_catalog_prd_versions_table --module=catalog`
`up()`:
```php
Schema::create('catalog_prd_versions', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->unsignedInteger('major')->nullable();
    $table->unsignedInteger('minor')->nullable();
    $table->text('body_markdown');
    $table->string('state')->default('draft');
    $table->timestampTz('frozen_at')->nullable();
    $table->boolean('has_image')->default(false);
    $table->boolean('has_mermaid')->default(false);
    $table->boolean('has_artifact')->default(false);
    $table->jsonb('mentions')->nullable();
    $table->timestampsTz();
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/PrdVersion.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\PrdVersionFactory;
use He4rt\Catalog\Enums\PrdVersionState;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $entry_id
 * @property int|null $major
 * @property int|null $minor
 * @property string $body_markdown
 * @property PrdVersionState $state
 * @property Carbon|null $frozen_at
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<PrdVersionFactory>
 */
#[UseFactory(PrdVersionFactory::class)]
#[Table(name: 'catalog_prd_versions')]
final class PrdVersion extends BaseModel
{
    protected static function booted(): void
    {
        static::saving(static function (PrdVersion $version): void {
            if ($version->isDirty('body_markdown')) {
                $version->fill(app(DeriveBodyFacts::class)->execute($version->body_markdown)->toColumns());
            }
        });
    }

    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected function casts(): array
    {
        return [
            'state' => PrdVersionState::class,
            'frozen_at' => 'datetime',
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
```
> A **numeração** (`major`/`minor`) e o congelamento são orquestrados por uma Action de publicação (fora deste plano — o ciclo de vida é o [ADR ciclo de vida do PRD](../../../../docs/adr/0011-ciclo-de-vida-do-prd-congela-ao-publicar.md)). Aqui garantimos só o schema e o cast.

- [ ] **Step 5: Escrever a factory**

`database/factories/PrdVersionFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\PrdVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrdVersion>
 */
final class PrdVersionFactory extends Factory
{
    protected $model = PrdVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory()->prd(),
            'major' => null,
            'minor' => null,
            'body_markdown' => fake()->paragraphs(3, true),
            'state' => PrdVersionState::Draft,
            'frozen_at' => null,
        ];
    }

    public function frozen(): self
    {
        return $this->state(fn (): array => [
            'state' => PrdVersionState::Frozen,
            'frozen_at' => now(),
        ]);
    }
}
```

- [ ] **Step 6: Rodar (passa)**

Run: `php artisan test --compact --filter=PrdVersionTest`
Expected: PASS.

- [ ] **Step 7: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model PrdVersion"
```

---

## Task 8: Model EntryLink (grafo entre Entradas)

**Files:**
- Create migration: `create_catalog_entry_links_table`
- Create: `src/Models/EntryLink.php`, `database/factories/EntryLinkFactory.php`
- Test: `tests/Feature/Models/EntryLinkTest.php`

**Interfaces:**
- Consumes: `Entry` (Task 4), `EntryLinkType` (Task 2).
- Produces: `EntryLink` com `fromEntry()`/`toEntry()`.

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/EntryLinkTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;

test('a link connects two existing entries with a type', function (): void {
    $from = Entry::factory()->create();
    $to = Entry::factory()->create();

    $link = EntryLink::factory()->create([
        'from_entry_id' => $from->id,
        'to_entry_id' => $to->id,
        'type' => EntryLinkType::Supersedes,
    ]);

    expect($link->fromEntry->is($from))->toBeTrue()
        ->and($link->toEntry->is($to))->toBeTrue()
        ->and($link->type)->toBe(EntryLinkType::Supersedes);
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=EntryLinkTest`
Expected: FAIL.

- [ ] **Step 3: Criar a migration**

Run: `php artisan make:migration create_catalog_entry_links_table --module=catalog`
`up()`:
```php
Schema::create('catalog_entry_links', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('from_entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->foreignUuid('to_entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->string('type');
    $table->timestampsTz();

    $table->unique(['from_entry_id', 'to_entry_id', 'type']);
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/EntryLink.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryLinkFactory;
use He4rt\Catalog\Enums\EntryLinkType;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $from_entry_id
 * @property string $to_entry_id
 * @property EntryLinkType $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $fromEntry
 * @property-read Entry $toEntry
 *
 * @extends BaseModel<EntryLinkFactory>
 */
#[UseFactory(EntryLinkFactory::class)]
#[Table(name: 'catalog_entry_links')]
final class EntryLink extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function fromEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'from_entry_id');
    }

    /** @return BelongsTo<Entry, $this> */
    public function toEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'to_entry_id');
    }

    protected function casts(): array
    {
        return [
            'type' => EntryLinkType::class,
        ];
    }
}
```

- [ ] **Step 5: Escrever a factory**

`database/factories/EntryLinkFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\EntryLinkType;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryLink>
 */
final class EntryLinkFactory extends Factory
{
    protected $model = EntryLink::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_entry_id' => Entry::factory(),
            'to_entry_id' => Entry::factory(),
            'type' => fake()->randomElement(EntryLinkType::cases()),
        ];
    }
}
```

- [ ] **Step 6: Rodar (passa) + Pint + PHPStan + commit**

```bash
php artisan test --compact --filter=EntryLinkTest
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model EntryLink"
```

---

## Task 9: Model EntryArtifact (Entrada só-artefato)

**Files:**
- Create migration: `create_catalog_entry_artifacts_table`
- Create: `src/Models/EntryArtifact.php`, `database/factories/EntryArtifactFactory.php`
- Test: `tests/Feature/Models/EntryArtifactTest.php`

**Interfaces:**
- Consumes: `Entry` (Task 4).
- Produces: `EntryArtifact` com `entry()`; `Entry::artifacts()` já declarada na Task 4.

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/EntryArtifactTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;

test('an artifact-only entry points to a url', function (): void {
    $entry = Entry::factory()->create();

    $artifact = EntryArtifact::factory()->for($entry)->create([
        'url' => 'https://waifuvault.moe/f/x.html',
    ]);

    expect($entry->artifacts()->count())->toBe(1)
        ->and($artifact->url)->toEndWith('.html');
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=EntryArtifactTest`
Expected: FAIL.

- [ ] **Step 3: Criar a migration**

Run: `php artisan make:migration create_catalog_entry_artifacts_table --module=catalog`
`up()`:
```php
Schema::create('catalog_entry_artifacts', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->string('url');
    $table->timestampsTz();
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/EntryArtifact.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\EntryArtifactFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $entry_id
 * @property string $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entry $entry
 *
 * @extends BaseModel<EntryArtifactFactory>
 */
#[UseFactory(EntryArtifactFactory::class)]
#[Table(name: 'catalog_entry_artifacts')]
final class EntryArtifact extends BaseModel
{
    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
```

- [ ] **Step 5: Escrever a factory**

`database/factories/EntryArtifactFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryArtifact>
 */
final class EntryArtifactFactory extends Factory
{
    protected $model = EntryArtifact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'url' => 'https://waifuvault.moe/f/'.fake()->slug(2).'.html',
        ];
    }
}
```

- [ ] **Step 6: Rodar (passa) + Pint + PHPStan + commit**

```bash
php artisan test --compact --filter=EntryArtifactTest
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model EntryArtifact"
```

---

## Task 10: Model Collection + pivot ordenado (com corpo)

**Files:**
- Create migrations: `create_catalog_collections_table`, `create_catalog_collection_entry_table`
- Create: `src/Models/Collection.php`, `database/factories/CollectionFactory.php`
- Test: `tests/Feature/Models/CollectionTest.php`

**Interfaces:**
- Consumes: `Entry` (Task 4), `DeriveBodyFacts` (Task 5), `Audience`/`Status` (Task 2), `He4rt\Identity\Users\User`.
- Produces: `Collection` com `entries()` BelongsToMany ordenado por `position`; corpo próprio (deriva fatos no save).

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Models/CollectionTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Collection;
use He4rt\Catalog\Models\Entry;

test('a collection carries a body and an ordered list of existing entries', function (): void {
    $first = Entry::factory()->create();
    $second = Entry::factory()->create();

    $collection = Collection::factory()->create([
        'body_markdown' => 'Bem-vindo! Comece por [pagamentos](RPQ:pagamentos/reference/x).',
    ]);
    $collection->entries()->attach([
        $first->id => ['position' => 1],
        $second->id => ['position' => 2],
    ]);

    expect($collection->entries()->orderByPivot('position')->pluck('catalog_entries.id')->all())
        ->toBe([$first->id, $second->id])
        ->and($collection->mentions)->toContain('RPQ:pagamentos/reference/x');
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=CollectionTest`
Expected: FAIL.

- [ ] **Step 3: Criar as migrations**

Run:
```bash
php artisan make:migration create_catalog_collections_table --module=catalog
php artisan make:migration create_catalog_collection_entry_table --module=catalog
```
`create_catalog_collections_table` `up()`:
```php
Schema::create('catalog_collections', static function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('slug')->unique();
    $table->string('title');
    $table->text('summary');
    $table->text('body_markdown')->nullable();
    $table->jsonb('audience');
    $table->foreignUuid('owner_id')->constrained('identity_users')->restrictOnDelete();
    $table->string('status');
    $table->boolean('has_image')->default(false);
    $table->boolean('has_mermaid')->default(false);
    $table->boolean('has_artifact')->default(false);
    $table->jsonb('mentions')->nullable();
    $table->timestampsTz();
});
```
`create_catalog_collection_entry_table` `up()`:
```php
Schema::create('catalog_collection_entry', static function (Blueprint $table): void {
    $table->foreignUuid('collection_id')->constrained('catalog_collections')->cascadeOnDelete();
    $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
    $table->unsignedInteger('position')->default(0);
    $table->primary(['collection_id', 'entry_id']);
});
```

- [ ] **Step 4: Escrever o model**

`src/Models/Collection.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Actions\DeriveBodyFacts;
use He4rt\Catalog\Database\Factories\CollectionFactory;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property string $summary
 * @property string|null $body_markdown
 * @property \Illuminate\Support\Collection<int, Audience> $audience
 * @property string $owner_id
 * @property Status $status
 * @property bool $has_image
 * @property bool $has_mermaid
 * @property bool $has_artifact
 * @property array<int, string>|null $mentions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Entry> $entries
 *
 * @extends BaseModel<CollectionFactory>
 */
#[UseFactory(CollectionFactory::class)]
#[Table(name: 'catalog_collections')]
final class Collection extends BaseModel
{
    protected static function booted(): void
    {
        static::saving(static function (Collection $collection): void {
            if ($collection->isDirty('body_markdown')) {
                $markdown = $collection->body_markdown ?? '';
                $collection->fill(app(DeriveBodyFacts::class)->execute($markdown)->toColumns());
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Trilha ordenada de Entradas existentes. @return BelongsToMany<Entry, $this> */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'catalog_collection_entry')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return parent::getActivitylogOptions()->logExcept(['body_markdown']);
    }

    protected function casts(): array
    {
        return [
            'audience' => AsEnumCollection::of(Audience::class),
            'status' => Status::class,
            'has_image' => 'boolean',
            'has_mermaid' => 'boolean',
            'has_artifact' => 'boolean',
            'mentions' => 'array',
        ];
    }
}
```

- [ ] **Step 5: Escrever a factory**

`database/factories/CollectionFactory.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Database\Factories;

use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Collection;
use He4rt\Identity\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
final class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(2, true));

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'title' => $title,
            'summary' => fake()->sentence(),
            'body_markdown' => null,
            'audience' => [Audience::Ti],
            'owner_id' => User::factory(),
            'status' => Status::Published,
        ];
    }
}
```

- [ ] **Step 6: Rodar (passa) + Pint + PHPStan + commit**

```bash
php artisan test --compact --filter=CollectionTest
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): model Collection com corpo e trilha ordenada"
```

---

## Task 11: Federação — ReconcileSnapshot (upsert + delete-absent)

**Files:**
- Create: `src/DTOs/Snapshot.php`, `src/DTOs/SnapshotEntry.php`, `src/Federation/ReconcileSnapshot.php`
- Test: `tests/Feature/Federation/ReconcileSnapshotTest.php`

**Interfaces:**
- Consumes: `Project`, `Entry`, `Document`, enums, `MintQualifiedId`.
- Produces: `Snapshot` (`acronym`, `entries: SnapshotEntry[]`), `SnapshotEntry` (`qualified_id`, `native_id`, `title`, `summary`, `purpose`, `format`, `department`, `body_markdown`, `git_pointer`), `ReconcileSnapshot::execute(Snapshot $snapshot): void`.

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Federation/ReconcileSnapshotTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\DTOs\SnapshotEntry;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Federation\ReconcileSnapshot;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;

function snapshotEntry(string $qualifiedId, string $native): SnapshotEntry
{
    return new SnapshotEntry(
        qualifiedId: $qualifiedId,
        nativeId: $native,
        title: 'T',
        summary: 'S',
        purpose: Purpose::Reference,
        format: Format::Reference,
        department: Area::Ti,
        bodyMarkdown: '# body',
        gitPointer: "docs/{$native}.md",
    );
}

test('reconcile upserts snapshot entries and deletes absent mirrors, sparing natives', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);

    // Espelho pré-existente que sumirá do snapshot.
    $stale = Entry::factory()->for($project, 'originProject')->create([
        'qualified_id' => 'RPQ:old', 'origin' => Origin::Mirror,
    ]);
    // Nativo do mesmo projeto — nunca pode ser tocado.
    $native = Entry::factory()->for($project, 'originProject')->create([
        'qualified_id' => 'RPQ:native', 'origin' => Origin::Native,
    ]);

    $snapshot = new Snapshot('RPQ', [
        snapshotEntry('RPQ:kept', 'kept'),   // novo
    ]);

    app(ReconcileSnapshot::class)->execute($snapshot);

    expect(Entry::query()->where('qualified_id', 'RPQ:kept')->exists())->toBeTrue()
        ->and(Entry::query()->whereKey($stale->id)->exists())->toBeFalse()   // espelho ausente removido
        ->and(Entry::query()->whereKey($native->id)->exists())->toBeTrue();  // nativo intacto
});

test('reconcile is idempotent', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ']);
    $snapshot = new Snapshot('RPQ', [snapshotEntry('RPQ:a', 'a'), snapshotEntry('RPQ:b', 'b')]);

    app(ReconcileSnapshot::class)->execute($snapshot);
    app(ReconcileSnapshot::class)->execute($snapshot);

    expect(Entry::query()->where('origin', Origin::Mirror)->count())->toBe(2);
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=ReconcileSnapshotTest`
Expected: FAIL.

- [ ] **Step 3: Escrever os DTOs**

`src/DTOs/SnapshotEntry.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;

final readonly class SnapshotEntry
{
    public function __construct(
        public string $qualifiedId,
        public string $nativeId,
        public string $title,
        public string $summary,
        public Purpose $purpose,
        public Format $format,
        public Area $department,
        public string $bodyMarkdown,
        public ?string $gitPointer,
    ) {}
}
```
`src/DTOs/Snapshot.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

final readonly class Snapshot
{
    /**
     * @param  array<int, SnapshotEntry>  $entries
     */
    public function __construct(
        public string $acronym,
        public array $entries,
    ) {}
}
```

- [ ] **Step 4: Escrever a action**

`src/Federation/ReconcileSnapshot.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ReconcileSnapshot
{
    /**
     * Snapshot COMPLETO de um projeto: upsert dos espelhos que vieram +
     * apaga os espelhos daquele projeto ausentes no snapshot. Escopo estrito
     * (project, origin=mirror): nunca toca nativos nem outros projetos.
     */
    public function execute(Snapshot $snapshot): void
    {
        $project = Project::query()->where('acronym', $snapshot->acronym)->firstOrFail();
        $systemOwnerId = $this->systemOwnerId();

        DB::transaction(function () use ($project, $snapshot, $systemOwnerId): void {
            $seen = [];

            foreach ($snapshot->entries as $item) {
                $existing = Entry::query()->where('qualified_id', $item->qualifiedId)->first();

                $entry = Entry::query()->updateOrCreate(
                    ['qualified_id' => $item->qualifiedId],
                    [
                        'native_id' => $item->nativeId,
                        'project_id' => $project->id,
                        'title' => $item->title,
                        'summary' => $item->summary,
                        'purpose' => $item->purpose,
                        'format' => $item->format,
                        'origin' => Origin::Mirror,
                        'department' => $item->department,
                        'audience' => $existing?->audience?->all() ?? [$item->department->value],
                        'status' => $existing?->status ?? Status::Published,
                        'owner_id' => $existing?->owner_id ?? $systemOwnerId,
                    ],
                );

                $entry->document()->updateOrCreate([], [
                    'body_markdown' => $item->bodyMarkdown,
                    'git_pointer' => $item->gitPointer,
                ]);

                $seen[] = $item->qualifiedId;
            }

            Entry::query()
                ->where('project_id', $project->id)
                ->where('origin', Origin::Mirror)
                ->whereNotIn('qualified_id', $seen)
                ->get()
                ->each->delete();

            $project->update(['last_synced_at' => now()]);
        });
    }

    /**
     * Espelhos sem dono declarado pertencem a um usuário-sistema dedicado.
     */
    private function systemOwnerId(): string
    {
        return User::query()->firstOrCreate(
            ['email' => 'federation@brainiac.system'],
            ['name' => 'Federação', 'password' => Hash::make(Str::random(40))],
        )->id;
    }
}
```
> ⚠ **Follow-up:** o `firstOrCreate` do usuário-sistema aqui é funcional mas deveria virar um **seeder** dedicado (`FederationSystemUserSeeder`) resolvido por `config('catalog.system_user_email')`, em vez de criado inline na reconciliação. `audience`/`status` de espelhos preservam o valor existente quando já havia Entrada; no primeiro ingest usam `[department]` / `Published` como default. Registrar como item de follow-up.

- [ ] **Step 5: Rodar (passa)**

Run: `php artisan test --compact --filter=ReconcileSnapshotTest`
Expected: PASS.

- [ ] **Step 6: Pint, PHPStan e commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
git add app-modules/catalog
git commit -m "feat(catalog): reconciliação de snapshot da federação"
```

---

## Task 12: Federação — webhook (rota + HMAC)

**Files:**
- Create: `src/Federation/VerifyWebhookSignature.php`, `src/Federation/Http/ReceiveSnapshotController.php`
- Modify: `routes/federation-routes.php`
- Test: `tests/Feature/Federation/ReceiveSnapshotTest.php`

**Interfaces:**
- Consumes: `ReconcileSnapshot` (Task 11), `Project` (hmac_secret).
- Produces: `POST /webhook/snapshot` que valida HMAC e dispara a reconciliação.

- [ ] **Step 1: Escrever o teste**

`tests/Feature/Federation/ReceiveSnapshotTest.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;

function signedPayload(Project $project): array
{
    $body = [
        'acronym' => $project->acronym,
        'entries' => [[
            'qualified_id' => $project->acronym.':kept', 'native_id' => 'kept',
            'title' => 'T', 'summary' => 'S', 'purpose' => 'reference',
            'format' => 'reference', 'department' => 'ti',
            'body_markdown' => '# body', 'git_pointer' => 'docs/kept.md',
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

    expect(Entry::query()->where('qualified_id', 'RPQ:kept')->exists())->toBeTrue();
});

test('rejects a wrong signature', function (): void {
    $project = Project::factory()->create(['acronym' => 'RPQ', 'hmac_secret' => 'top-secret']);
    [$body] = signedPayload($project);

    $this->withHeader('X-Signature', 'wrong')
        ->postJson('/webhook/snapshot', $body)
        ->assertForbidden();
});
```

- [ ] **Step 2: Rodar (falha)**

Run: `php artisan test --compact --filter=ReceiveSnapshotTest`
Expected: FAIL.

- [ ] **Step 3: Escrever o verificador HMAC**

`src/Federation/VerifyWebhookSignature.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation;

final class VerifyWebhookSignature
{
    public function matches(string $payload, string $signature, string $secret): bool
    {
        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
```

- [ ] **Step 4: Escrever o controller**

`src/Federation/Http/ReceiveSnapshotController.php`:
```php
<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation\Http;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\DTOs\SnapshotEntry;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Federation\ReconcileSnapshot;
use He4rt\Catalog\Federation\VerifyWebhookSignature;
use He4rt\Catalog\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReceiveSnapshotController
{
    public function __invoke(
        Request $request,
        VerifyWebhookSignature $verifier,
        ReconcileSnapshot $reconcile,
    ): JsonResponse {
        $acronym = (string) $request->input('acronym');
        $project = Project::query()->where('acronym', $acronym)->firstOrFail();

        abort_unless(
            is_string($project->hmac_secret) && $verifier->matches(
                $request->getContent(),
                (string) $request->header('X-Signature'),
                $project->hmac_secret,
            ),
            Response::HTTP_FORBIDDEN,
        );

        /** @var array<int, array<string, string>> $rawEntries */
        $rawEntries = $request->input('entries', []);

        $entries = array_map(static fn (array $e): SnapshotEntry => new SnapshotEntry(
            qualifiedId: $e['qualified_id'],
            nativeId: $e['native_id'],
            title: $e['title'],
            summary: $e['summary'],
            purpose: Purpose::from($e['purpose']),
            format: Format::from($e['format']),
            department: Area::from($e['department']),
            bodyMarkdown: $e['body_markdown'],
            gitPointer: $e['git_pointer'] ?? null,
        ), $rawEntries);

        $reconcile->execute(new Snapshot($acronym, $entries));

        return response()->json(['status' => 'ok']);
    }
}
```

- [ ] **Step 5: Escrever a rota**

`routes/federation-routes.php`:
```php
<?php

declare(strict_types=1);

use He4rt\Catalog\Federation\Http\ReceiveSnapshotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/snapshot', ReceiveSnapshotController::class);
```

- [ ] **Step 6: Rodar (passa)**

Run: `php artisan test --compact --filter=ReceiveSnapshotTest`
Expected: PASS.

- [ ] **Step 7: Bateria completa do módulo + Pint + PHPStan + commit**

```bash
vendor/bin/pint --dirty --format agent
(cd app-modules/catalog && ../../vendor/bin/phpstan analyse --ansi)
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact --filter=Catalog
git add app-modules/catalog
git commit -m "feat(catalog): webhook de federação com verificação HMAC"
```

---

## Self-Review

**Cobertura do spec:**
- Tabelas `projects`/`entries`/`documents`/`prd_versions`/`entry_links`/`entry_project`/`entry_artifacts`/`collections`/`collection_entry` → Tasks 3, 4, 6, 7, 8, 9, 10 ✓
- Enums (`Purpose`/`Format`/`Origin`/`Area`/`Audience`/`Status`/`EntryLinkType`/`PrdVersionState`) → Task 2 ✓
- Invariante origem-na-faceta → Task 4 ✓
- Fatos do corpo derivados + guardados → Tasks 5, 6, 7, 10 ✓
- Federação (upsert + delete-absent, escopo mirror, idempotente) → Task 11 ✓; transporte webhook + HMAC → Task 12 ✓
- Single-tenant (sem `tenant_id`) → respeitado em todas as migrations ✓
- Módulo `catalog` com federação em `Federation/` → Tasks 1, 11, 12 ✓

**Lacunas conhecidas (fora do escopo deste plano, follow-up):**
- Política de `owner_id`/`audience`/`status` para espelhos (usuário-sistema) — nota na Task 11.
- Numeração/congelamento de `PrdVersion` (Action de publicação) — [ADR ciclo de vida do PRD].
- Resolução de menções no render (reescrita de links) e invalidação de cache — camada de render/apresentação.
- Autoria por guideline / parse de front-matter — item #4 do spec, na presentation/ingest.

**Consistência de tipos:** relações e assinaturas conferidas entre tasks (`originProject`/`owner`/`document`/`prdVersions`/`projects`/`artifacts`/`entries`; `MintQualifiedId::execute`, `DeriveBodyFacts::execute`, `ReconcileSnapshot::execute`, `VerifyWebhookSignature::matches`). Nomes de tabela batem com os `#[Table]`.
