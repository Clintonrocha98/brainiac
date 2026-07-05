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
